<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
// use FiveamCode\LaravelNotionApi\Notion;
use FiveamCode\LaravelNotionApi\Query\Filters\Filter;
use FiveamCode\LaravelNotionApi\Query\Sorting;

use FiveamCode\LaravelNotionApi\Entities\Blocks\Block;
use FiveamCode\LaravelNotionApi\Entities\Blocks\BulletedListItem;
use FiveamCode\LaravelNotionApi\Entities\Blocks\Embed;
use FiveamCode\LaravelNotionApi\Entities\Blocks\File;
use FiveamCode\LaravelNotionApi\Entities\Blocks\HeadingOne;
use FiveamCode\LaravelNotionApi\Entities\Blocks\HeadingThree;
use FiveamCode\LaravelNotionApi\Entities\Blocks\HeadingTwo;
use FiveamCode\LaravelNotionApi\Entities\Blocks\Image;
use FiveamCode\LaravelNotionApi\Entities\Blocks\NumberedListItem;
use FiveamCode\LaravelNotionApi\Entities\Blocks\Paragraph;
use FiveamCode\LaravelNotionApi\Entities\Blocks\Pdf;
use FiveamCode\LaravelNotionApi\Entities\Blocks\ToDo;
use FiveamCode\LaravelNotionApi\Entities\Blocks\Toggle;
use FiveamCode\LaravelNotionApi\Entities\Blocks\Video;

use FiveamCode\LaravelNotionApi\Entities\Blocks\Block as BlockEntity;

use FiveamCode\LaravelNotionApi\Endpoints\Database;
use Notion;

class NotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $notion = new Notion("secret_uSs7fTGZXxIz2OGqklQnc8ACCxFP9iVraeroXI0Laao");

        $type = $request->type;
        $id = $request->id;

        switch($type) {
            // database
            case 'db':
                $field_name = $request->field_name;
                $keyword = $request->keyword;
                
                if ($field_name && $keyword) {
                    $filters = new Collection();
                    $filters->add(
                        Filter::rawFilter(
                            $field_name, 
                            [
                                'rich_text' => [
                                    'contains' => $keyword
                                ]
                            ]
                        )
                    );
    
                    $result = Notion::database($id)
                        ->filterBy($filters)
                        ->query()
                        ->asCollection();
                    return response(['data' => $result]);
                }
                else {
                    $result = Notion::database($id)
                        ->query()
                        ->asCollection();
                    return response(['data' => $result]);
                }
                break;

            // block
            case 'block':
                $include_child = $request->include_child;
                if ($include_child) {
                    $blockContents = $this->getBlockIncludingChilds($id);
                    return response(['data' => $blockContents]);
                }
                else {
                    $block = Notion::block($id)
                        // ->retrieve();
                        // ->getRawContent()['page_id'];

                        ->children()
                        ->asCollection();

                        // ->asTextCollection();
                    return response(['data' => $block]);
                }
                break;

            case 'page':
                $page = Notion::pages()->find($id);
                return response(['data' => $page]);
                break;

            default: 
                break;
        }

        return response(['test' => "YAY!", 'data' => $request->all()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $type = $request->type;
        $id = $request->id;

        switch($type) {
            // database
            case 'db':
                $paragraph = Paragraph::create('New TextBlock By API');
                Notion::block($id)->append($paragraph);
                return response(['success' => "true"]);
                break;

            // block
            case 'block':
                $contents = $request->contents;
                $paragraph = Paragraph::create($contents);
                $block = Notion::block($id)->append($paragraph);
                return response(['data' => $block]);
                break;

            case 'page':
                break;

            default: 
                break;
        }

        return response(['test' => "store", 'data' => $request->all()]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $blockRawResponse = Notion::block($id)->retrieve()->getRawResponse();
        // update contents

        $newBlockEntity = new BlockEntity($blockRawResponse);
        $updatedBlock = Notion::block($id)->update(new BlockEntity($blockRawResponse));
        return response(['message' => "DELETE testing!", 'success' => $updatedBlock]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // update "archived" field to 'true'
        $blockRawResponse = Notion::block($id)->retrieve()->getRawResponse();
        $blockRawResponse["archived"] = true;

        $newBlockEntity = new BlockEntity($blockRawResponse);
        $updatedBlock = Notion::block($id)->update(new BlockEntity($blockRawResponse));
        return response(['message' => "DELETE testing!", 'success' => $updatedBlock]);
    }

    private function getBlockIncludingChilds($blockId, $contentType = null)
    {
        if ($contentType) {
            switch ($contentType) {
                case 'page':
                    $page = Notion::pages()->find($blockId);
                    $pageContents = [
                        'id' => $page->getId(),
                        'type' => 'page',
                        'plain_text' => $page->getTitle()
                    ];
                    return $pageContents;
                    break;

                case 'synced_block':
                    $contents[$index] = $this->getBlockIncludingChilds($pageId, 'page');
                    break;
            }
        }

        $blocks = Notion::block($blockId)
            ->children()
            ->asCollection()
            ->toArray();
        
        $contents = [];
        
        if (count($blocks)) {
            $index = 0;
            foreach ($blocks as $block) {
                $contents[$index] = [];

                switch($block->getType()) {
                    case "link_to_page":
                        if (array_key_exists('page_id', $block->getRawContent())) {
                            $pageId = $block->getRawContent()['page_id'];
                            $contents[$index] = $this->getBlockIncludingChilds($pageId, 'page');
                        }
                        else {
                            $contents[$index]['id'] = $block->getId();
                            $contents[$index]['type'] = $block->getType();
                            $contents[$index]['plain_text'] = '';
                        }
                        break;

                    case "synced_block":
                        if (array_key_exists('synced_from', $block->getRawContent())) {
                            $syncedBlockId = $block->getRawContent()['synced_from']['block_id'];
                            // $contents[$index] = $this->getBlockIncludingChilds($syncedBlockId);
                            $contents[$index] = $this->getBlockIncludingChilds($syncedBlockId);
                            $contents[$index] = $contents[$index][0];
                        }
                        break;

                    case "paragraph":
                        $contents[$index]['id'] = $block->getId();
                        $contents[$index]['type'] = $block->getType();
                        $contents[$index]['plain_text'] = $block->asText();
                        break;

                    default: 
                        $contents[$index]['id'] = $block->getId();
                        $contents[$index]['type'] = $block->getType();
                        $contents[$index]['plain_text'] = $block->asText();
                        break;
                }
                
                if ($block->hasChildren()) {
                    if ($block->getType() != 'synced_block') {
                        $contents[$index]['children'] = $this->getBlockIncludingChilds($block->getId());
                    }
                }

                $index ++;
            }
        }

        return $contents;
    }
}
