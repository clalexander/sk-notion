<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
// use FiveamCode\LaravelNotionApi\Notion;
use FiveamCode\LaravelNotionApi\Query\Filters\Filter;
use FiveamCode\LaravelNotionApi\Query\Sorting;
use FiveamCode\LaravelNotionApi\Query\StartCursor;

use FiveamCode\LaravelNotionApi\Entities\Page;
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
        $limit = $request->limit ? $request->limit : 100;
        $offset = $request->offset ? $request->offset : '';

        $startCursor = null;
        if ($offset) {
            $startCursor = new StartCursor($offset);
        }
        
        $type = $request->type;
        $id = $request->id;
        $orderBy = $request->order_by;

        switch($type) {
            // database
            case 'db':
                $field_name = $request->field_name;
                $keyword = $request->keyword;
                
                if ($field_name && $keyword) {
                    $filters = new Collection();

                    switch($field_name) {
                        case "Book":
                        case "Status":
                        case "Language":
                            $filters->add(
                                Filter::rawFilter(
                                    $field_name, 
                                    [
                                        'select' => [
                                            'equals' => $keyword
                                        ]
                                    ]
                                )
                            );
                            // select
                            break;

                        case "Category":
                            // multi-select
                            $filters->add(
                                Filter::rawFilter(
                                    $field_name, 
                                    [
                                        'multi_select' => [
                                            'contains' => $keyword
                                        ]
                                    ]
                                )
                            );
                            break;

                        default:
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
                    }
                    
                    $result = Notion::database($id)
                        ->filterBy($filters)
                        ->limit($limit)
                        // ->offset($startCursor)
                        ->query()
                        ->asCollection();
                    return response(['data' => $result]);
                }
                else {
                    $result = Notion::database($id)
                        ->limit($limit)
                        // ->offset($startCursor)
                        ->query()
                        ->asCollection();
                    return response(['data' => $result]);
                }
                break;

            case "multi_filters":
                $filter_str = $request->filters;
                
                if ($filter_str) {
                    $filter_options = json_decode($filter_str);
                    $filters = new Collection();
                    foreach($filter_options as $key => $value) {
                        switch($key) {
                            case "Book":
                            case "Status":
                            case "Language":
                                $filters->add(
                                    Filter::rawFilter(
                                        $key, 
                                        [
                                            'select' => [
                                                'equals' => $value
                                            ]
                                        ]
                                    )
                                );
                                // select
                                break;
    
                            case "Category":
                                // multi-select
                                $filters->add(
                                    Filter::rawFilter(
                                        $key, 
                                        [
                                            'multi_select' => [
                                                'contains' => $value
                                            ]
                                        ]
                                    )
                                );
                                break;
    
                            default:
                                $filters->add(
                                    Filter::rawFilter(
                                        $key, 
                                        [
                                            'rich_text' => [
                                                'contains' => $value
                                            ]
                                        ]
                                    )
                                );
                                break;
                        }
                    }
    
                    $result = Notion::database($id)
                        ->filterBy($filters);

                    if ($orderBy) {
                        $sortings = new Collection();
                        $sortings->add(
                            Sorting::propertySort($orderBy, 'ascending')
                        );
                        $result = $result->sortBy($sortings);
                    }
                    $result = $result->limit($limit);

                    if ($startCursor) {
                        $result = $result->offset($startCursor);
                    }
                    $result = $result->query();

                    $rawResponse = $result->getRawResponse();

                    $nextCursor = $rawResponse["next_cursor"];
                    $hasMore = $rawResponse["has_more"];

                    $data = $result->asCollection();

                    return response([
                        'data' => $data, 
                        'has_more' => $hasMore, 
                        'next_cursor' => $nextCursor
                    ]);
                }
                else {
                    $result = Notion::database($id)
                        ->limit($limit);

                    if ($startCursor) {
                        $result = $result->offset($startCursor);
                    }
                    $result = $result->query();

                    $rawResponse = $result->getRawResponse();

                    $nextCursor = $rawResponse["next_cursor"];
                    $hasMore = $rawResponse["has_more"];

                    $data = $result->asCollection();

                    return response([
                        'data' => $result,
                        'has_more' => $hasMore,
                        'next_cursor' => $nextCursor
                    ]);
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

            // blocks list
            case 'blocks_list':
                $include_child = $request->include_child;
                $blocks = json_decode($request->blocks);

                if (count($blocks)) {
                    $result = [];
                    foreach ($blocks as $block_id) {
                        if ($include_child) {
                            $result[$block_id] = $this->getBlockIncludingChilds($block_id);
                        }
                        else {
                            $result[$block_id] = Notion::block($block_id)
                                ->children()
                                ->asCollection();
                        }
                    }
                    return response(['data' => $result]);
                }

                return response(['success' => false, 'message' => 'Blocks cannot be empty.']);
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
                $pageOptions = $request->options;
                // add new page to db
                $page = new Page();

                if (array_key_exists("Heading", $pageOptions) && $pageOptions["Heading"]) {
                    $page->setTitle("﻿Heading", $pageOptions["Heading"]);
                }
                if (array_key_exists("HeadingOrder", $pageOptions) && $pageOptions["HeadingOrder"]) {
                    $page->setText("HeadingOrder", $pageOptions["HeadingOrder"]);
                }
                if (array_key_exists("Keywords", $pageOptions) && $pageOptions["Keywords"]) {
                    $page->setText("Keywords", $pageOptions["Keywords"]);
                }
                if (array_key_exists("Book", $pageOptions) && $pageOptions["Book"]) {
                    $page->setSelect("Book", $pageOptions["Book"]);
                }
                if (array_key_exists("Passage", $pageOptions) && $pageOptions["Passage"]) {
                    $page->setText("Passage", $pageOptions["Passage"]);
                }
                if (array_key_exists("RelatedPassage", $pageOptions) && $pageOptions["RelatedPassage"]) {
                    $page->setText("RelatedPassage", $pageOptions["RelatedPassage"]);
                }
                if (array_key_exists("BeginWord", $pageOptions) && $pageOptions["BeginWord"]) {
                    $page->setText("BeginWord", $pageOptions["BeginWord"]);
                }
                if (array_key_exists("EndWord", $pageOptions) && $pageOptions["EndWord"]) {
                    $page->setText("EndWord", $pageOptions["EndWord"]);
                }
                if (array_key_exists("TextualBase", $pageOptions) && $pageOptions["TextualBase"]) {
                    $page->setText("TextualBase", $pageOptions["TextualBase"]);
                }
                if (array_key_exists("Reference", $pageOptions) && $pageOptions["Reference"]) {
                    $page->setText("Reference", $pageOptions["Reference"]);
                }
                if (array_key_exists("StartPage", $pageOptions) && $pageOptions["StartPage"]) {
                    $page->setText("StartPage", $pageOptions["StartPage"]);
                }
                if (array_key_exists("StartParagraph", $pageOptions) && $pageOptions["StartParagraph"]) {
                    $page->setText("StartParagraph", $pageOptions["StartParagraph"]);
                }
                if (array_key_exists("EndPage", $pageOptions) && $pageOptions["EndPage"]) {
                    $page->setText("EndPage", $pageOptions["EndPage"]);
                }
                if (array_key_exists("EndParagraph", $pageOptions) && $pageOptions["EndParagraph"]) {
                    $page->setText("EndParagraph", $pageOptions["EndParagraph"]);
                }
                if (array_key_exists("Category", $pageOptions) && $pageOptions["Category"]) {
                    $category = array_filter($pageOptions["Category"]);
                    if (count($category) > 0) {
                        $page->setMultiSelect("Category", $pageOptions["Category"]);
                    }
                }
                if (array_key_exists("VideoURL", $pageOptions) && $pageOptions["VideoURL"]) {
                    $page->setText("VideoURL", $pageOptions["VideoURL"]);
                }
                if (array_key_exists("VideoTitle", $pageOptions) && $pageOptions["VideoTitle"]) {
                    $page->setText("VideoTitle", $pageOptions["VideoTitle"]);
                }
                if (array_key_exists("VideoTime", $pageOptions) && $pageOptions["VideoTime"]) {
                    $page->setText("VideoTime", $pageOptions["VideoTime"]);
                }
                if (array_key_exists("Status", $pageOptions) && $pageOptions["Status"]) {
                    $page->setSelect("Status", $pageOptions["Status"]);
                }
                if (array_key_exists("NoteOrder", $pageOptions) && $pageOptions["NoteOrder"]) {
                    $page->setText("NoteOrder", $pageOptions["NoteOrder"]);
                }
                if (array_key_exists("Language", $pageOptions) && $pageOptions["Language"]) {
                    $page->setSelect("Language", $pageOptions["Language"]);
                }
                if (array_key_exists("BCV1", $pageOptions) && $pageOptions["BCV1"]) {
                    $page->setText("BCV1", $pageOptions["BCV1"]);
                }
                if (array_key_exists("BCV2", $pageOptions) && $pageOptions["BCV2"]) {
                    $page->setText("BCV2", $pageOptions["BCV2"]);
                }

                $result = Notion::pages()->createInDatabase($id, $page);
                
                return response(['page_id' => $result->getId(), 'success' => true]);
                break;

            // block
            case 'block':
                $contents = $request->contents;
                if (empty($contents)) {
                    return response(['success' => false, 'message' => 'Contents cannot be null or empty.']);
                }

                $content_type = $request->content_type;

                switch ($content_type) {
                    case "Paragraph":
                        $block = Paragraph::create($contents);
                        break;

                    case "BulletedListItem":
                        $block = BulletedListItem::create($contents);
                        break;

                    case "HeadingOne":
                        $block = HeadingOne::create($contents);
                        break;

                    case "HeadingTwo":
                        $block = HeadingTwo::create($contents);
                        break;

                    case "HeadingThree":
                        $block = HeadingThree::create($contents);
                        break;

                    case "NumberedListItem":
                        $block = NumberedListItem::create($contents);
                        break;

                    case "ToDo":
                        $block = ToDo::create($contents);
                        break;

                    // $toggle = Toggle::create(['New TextBlock', 'New TextBlock']);
                    // $embed = Embed::create('https://5amco.de', 'Testcaption');
                    // $image = Image::create('https://images.unsplash.com/photo-1593642533144-3d62aa4783ec?ixlib=rb-1.2.1&q=85&fm=jpg&crop=entropy&cs=srgb', 'Testcaption');
                    // $file = File::create('https://images.unsplash.com/photo-1593642533144-3d62aa4783ec?ixlib=rb-1.2.1&q=85&fm=jpg&crop=entropy&cs=srgb', 'Testcaption');
                    // $video = Video::create('https://www.w3schools.com/html/mov_bbb.mp4', 'TestCaption');
                    // $pdf = Pdf::create('https://notion.so/testpdf.pdf', 'TestCaption');
                }

                $addedBlock = Notion::block($id)->append($block);

                return response(['success' => true, 'block' => $addedBlock]);
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
        $pageId = $id;
        $property_name = $request->property_name;
        $property_value = $request->property_value;
        
        if ($property_value) {
            $page = new Page();
            $page->setId($pageId);

            switch($property_name) {
                case "Heading":
                        $page->setTitle("﻿Heading", $property_value);
                    break;

                case "HeadingOrder":
                case "Keywords":
                case "Passage":
                case "RelatedPassage":
                case "BeginWord":
                case "EndWord":
                case "TextualBase":
                case "Reference":
                case "StartPage":
                case "StartParagraph":
                case "EndPage":
                case "EndParagraph":
                case "VideoURL":
                case "VideoTitle":
                case "VideoTime":
                case "NoteOrder":
                case "BCV1":
                case "BCV2":
                    $page->setText($property_name, $property_value);
                    break;

                case "Book":
                case "Status":
                case "Language":
                    $page->setSelect($property_name, $property_value);
                    break;

                case "Category":
                    $category = array_filter($property_value);
                    if (count($category) > 0) {
                        $page->setMultiSelect($property_name, $property_value);
                    }
                    break;
            }

            $updatedPage = Notion::pages()->update($page);
            return response(['page' => $updatedPage]);
        }
        
        return response(['success'=> false,"message"=>'Property Value cannot be null']);
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

    /**
     * Get Full Contents (recursive call)
     */
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

                // case 'table_row':
                //     $block = Notion::block($blockId);
                //     dd($block);
                //     break;

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
                            if (is_null($block->getRawContent()['synced_from'])) {
                                $contents[$index] = $this->getBlockIncludingChilds($block->getId());
                                $contents[$index] = $contents[$index][0];
                            }
                            else {
                                $syncedBlockId = $block->getRawContent()['synced_from']['block_id'];
                                // $contents[$index] = $this->getBlockIncludingChilds($syncedBlockId);
                                $contents[$index] = $this->getBlockIncludingChilds($syncedBlockId);
                                $contents[$index] = $contents[$index][0];
                            }
                        }
                        break;

                    case "table_row":
                        // dd($block->getRawContent()["cells"]);
                        $contents[$index]['id'] = $block->getId();
                        $contents[$index]['type'] = $block->getType();
                        $contents[$index]['data'] = $block->getRawContent()["cells"];
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
