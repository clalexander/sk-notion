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
                $field_name = $request->field;
                $keyword = $request->keyword;

                if ($keyword) {
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
                $block = Notion::block($id)
                    // ->retrieve();
                    ->children()
                    ->asCollection();
                    // ->asTextCollection();
                return response(['data' => $block]);
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
                $paragraph = Paragraph::create('New TextBlock By API');
                Notion::block($id)->append($paragraph);

                // $block = Notion::block($id)
                //     ->children()
                //     ->asCollection();
                return response(['data' => $block]);
                break;

            case 'page':
                break;

            default: 
                break;
        }

        return response(['test' => "YAY!", 'data' => $request->all()]);
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
}
