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
use Illuminate\Support\Facades\Storage;
use Notion;
use Exception;
use Cache;

class NotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Cache::flush();
        $useCache = $request->use_cache ?? false;

        $cacheKey = 'index';

        $limit = $request->limit ? $request->limit : 100;
        $cacheKey .= '_' . $limit;
        $offset = $request->offset ? $request->offset : '';
        $cacheKey .= '_' . $offset;

        $startCursor = null;
        if ($offset) {
            $startCursor = new StartCursor($offset);
        }
        
        $type = $request->type;
        $id = $request->id;
        $orderBy = $request->order_by;

        $cacheKey .= '_' . $type;
        $cacheKey .= '_' . $id;
        $cacheKey .= '_' . $orderBy;

        switch($type) {
            // database
            case 'db':
                $field_name = $request->field_name;
                $keyword = $request->keyword;
                
                $cacheKey .= '_' . $field_name;
                $cacheKey .= '_' . $keyword;
                
                if ($field_name && $keyword) {
                    $filters = new Collection();
                    if ($useCache && Cache::has($cacheKey)) {
                        return response(['data' => Cache::get($cacheKey), 'cached' => true]);
                    }

                    switch($field_name) {
                        case "Heading":
                            $filters->add(
                                Filter::rawFilter(
                                    "?Heading",
                                    [
                                        'rich_text' => [
                                            'contains' => $keyword
                                        ]
                                    ]
                                )
                            );
                            break;

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

                    if ($useCache) {
                        Cache::set($cacheKey,$result,3600);
                    }
                    return response(['data' => $result, 'cached' => false]);
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
                
                $cacheKey .= '_' . $filter_str;
                
                if ($useCache && Cache::has($cacheKey)) {
                    $cachedResult = Cache::get($cacheKey);
                    $cachedResult['cached'] = true;
                    $cachedResult['cache_key'] = $cacheKey;
                    return response($cachedResult);
                }

                if ($filter_str) {
                    $filter_options = json_decode($filter_str);
                    $filters = new Collection();

                    // check if any duplicated filter_options, if then separate them as multiple filters
                    $filter_arr = $this->processFilters($filter_options);

                    foreach ($filter_options as $key => $value) {
                        switch($key) {
                            case "Book":
                            case "Status":
                            case "Language":
                                if (is_array($value) && count($value) > 0) {
                                    foreach ($value as $subVal) {
                                        $filters->add(
                                            Filter::rawFilter(
                                                $key, 
                                                [
                                                    'select' => [
                                                        'equals' => $subVal
                                                    ]
                                                ]
                                            )
                                        );
                                    }
                                }
                                else {
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
                                }
                                // select
                                break;
    
                            case "Category":
                                // multi-select
                                if (is_array($value) && count($value) > 0) {
                                    foreach ($value as $subVal) {
                                        $filters->add(
                                            Filter::rawFilter(
                                                $key, 
                                                [
                                                    'multi_select' => [
                                                        'contains' => $subVal
                                                    ]
                                                ]
                                            )
                                        );
                                    }
                                }
                                else {
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
                                }
                                break;
    
                            default:
                                if (is_array($value) && count($value) > 0) {
                                    foreach ($value as $subVal) {
                                        $filters->add(
                                            Filter::rawFilter(
                                                $key, 
                                                [
                                                    'rich_text' => [
                                                        'contains' => $subVal
                                                    ]
                                                ]
                                            )
                                        );
                                    }
                                }
                                else {
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
                                }
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
                    
                    $response = [
                        'data' => $data, 
                        'has_more' => $hasMore, 
                        'next_cursor' => $nextCursor
                    ];

                    if ($useCache) {
                        Cache::set($cacheKey, $response, 3600);
                    }
                    $response['cached'] = false;
                    $response['cache_key'] = $cacheKey;

                    return response($response);
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
                    $response = [
                        'data' => $result,
                        'has_more' => $hasMore,
                        'next_cursor' => $nextCursor
                    ];

                    if ($useCache) {
                        Cache::set($cacheKey, $response, 3600);
                    }

                    $response['cached'] = false;
                    $response['cache_key'] = $cacheKey;
                    return response($response);
                }
                break;

            // block
            case 'block':
                $include_child = $request->include_child;
                if ($include_child) {
                    $block = Notion::block($id)
                        // ->children()
                        ->retrieve();
                    $blockContents = $this->getBlockIncludingChilds($id);
                    return response(['block' => $block, 'children' => $blockContents]);
                    // return response(['block' => $block]);
                    // return response(['children' => $blockContents]);
                    // $childrens = Notion::block($id)
                    //     ->children()
                    //     ->asCollection();
                    // return response(['block' => $block, 'children' => $childrens]);

                }
                else {
                    $block = Notion::block($id)
                        ->children()
                        ->asCollection();
                    return response(['data' => $block]);
                }
                break;

            // blocks list
            case 'blocks_list':
                $include_child = $request->include_child;
                $blocks = json_decode($request->blocks);

                if (!is_null($blocks) && count($blocks)) {

                    $cacheKey .= '_' . $request->blocks;
                    $cacheKey .= '_' . $request->include_child;

                    if ($useCache && Cache::has($cacheKey)) {
                        return response(['data' => Cache::get($cacheKey), 'cached' => true, 'cache_key' => $cacheKey]);
                    }

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
                    if ($useCache) {
                        Cache::set($cacheKey, $result, 3600);
                    }
                    return response(['data' => $result, 'cached' => false, 'cache_key' => $cacheKey]);
                }

                return response(['success' => false, 'message' => 'Blocks cannot be empty.']);
                break;

            case 'page':
                $page = Notion::pages()->find($id);
                return response(['data' => $page]);
                break;

            case 'quick_find':
                $searchText = $request->search_text;
                $cacheKey .= '_' . $searchText;
                if ($useCache && Cache::has($cacheKey)) {
                    return response(['data' => Cache::get($cacheKey), 'cached' => true, 'cache_key' =>$cacheKey]);
                }
                $result = Notion::search($searchText)->query()->asCollection();
                if ($useCache) {
                    Cache::set($cacheKey,$result,3600);
                }
                return response(['data' => $result, 'cached' => false, 'cache_key' =>$cacheKey ]);
                break;

            case 'query':
                $filter_str = $request->filters;
                
                $cacheKey .= '_' . $filter_str;
                
                if ($useCache && Cache::has($cacheKey)) {
                    $cachedResult = Cache::get($cacheKey);
                    $cachedResult['cached'] = true;
                    $cachedResult['cache_key'] = $cacheKey;
                    return response($cachedResult);
                }

                if ($filter_str) {
                    $filter_options = json_decode($filter_str);
                    $filters = new Collection();

                    // check if any duplicated filter_options, if then separate them as multiple filters
                    $filter_arr = $this->processFilters($filter_options);

                    foreach ($filter_options as $key => $value) {
                        switch($key) {
                            // case "Book":
                            // case "Status":
                            // case "Language":
                            //     if (is_array($value) && count($value) > 0) {
                            //         foreach ($value as $subVal) {
                            //             $filters->add(
                            //                 Filter::rawFilter(
                            //                     $key, 
                            //                     [
                            //                         'select' => [
                            //                             'equals' => $subVal
                            //                         ]
                            //                     ]
                            //                 )
                            //             );
                            //         }
                            //     }
                            //     else {
                            //         $filters->add(
                            //             Filter::rawFilter(
                            //                 $key, 
                            //                 [
                            //                     'select' => [
                            //                         'equals' => $value
                            //                     ]
                            //                 ]
                            //             )
                            //         );
                            //     }
                            //     // select
                            //     break;

                            // case "Category":
                            //     // multi-select
                            //     if (is_array($value) && count($value) > 0) {
                            //         foreach ($value as $subVal) {
                            //             $filters->add(
                            //                 Filter::rawFilter(
                            //                     $key, 
                            //                     [
                            //                         'multi_select' => [
                            //                             'contains' => $subVal
                            //                         ]
                            //                     ]
                            //                 )
                            //             );
                            //         }
                            //     }
                            //     else {
                            //         $filters->add(
                            //             Filter::rawFilter(
                            //                 $key, 
                            //                 [
                            //                     'multi_select' => [
                            //                         'contains' => $value
                            //                     ]
                            //                 ]
                            //             )
                            //         );
                            //     }
                            //     break;

                            default:
                                if (is_array($value) && count($value) > 0) {
                                    foreach ($value as $subVal) {
                                        $filters->add(
                                            Filter::rawFilter(
                                                $key, 
                                                [
                                                    'rich_text' => [
                                                        'contains' => $subVal
                                                    ]
                                                ]
                                            )
                                        );
                                    }
                                }
                                else {
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
                                }
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

                    // $data = $result->asCollection();
                    $data = $rawResponse["results"];
                    foreach ($data as &$block) {
                        if (isset($block["properties"]["Book"]) && isset($block["properties"]["Book"]["relation"]) && count($block["properties"]["Book"]["relation"])) {
                            $bookId = $block["properties"]["Book"]["relation"][0]["id"];
                            if (isset($bookId) && !is_null($bookId)) {
                                $bookDetails = Notion::pages()->find($bookId)->getRawResponse();
                                $block["properties"]["Book"]["details"] = $bookDetails;
                            }
                        }
                        if (isset($block["properties"]["Essays"]) && isset($block["properties"]["Essays"]["relation"]) && count($block["properties"]["Essays"]["relation"])) {
                            $essaysId = $block["properties"]["Essays"]["relation"][0]["id"];
                            if (isset($essaysId) && !is_null($essaysId)) {
                                $essaysDetails = Notion::pages()->find($essaysId)->getRawResponse();
                                $block["properties"]["Essays"]["details"] = $essaysDetails;
                            }
                        }
                    }
                    
                    $response = [
                        'data' => $data, 
                        'has_more' => $hasMore, 
                        'next_cursor' => $nextCursor
                    ];

                    if ($useCache) {
                        Cache::set($cacheKey, $response, 3600);
                    }
                    $response['cached'] = false;
                    $response['cache_key'] = $cacheKey;

                    return response($response);
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
                    $response = [
                        'data' => $result,
                        'has_more' => $hasMore,
                        'next_cursor' => $nextCursor
                    ];

                    if ($useCache) {
                        Cache::set($cacheKey, $response, 3600);
                    }

                    $response['cached'] = false;
                    $response['cache_key'] = $cacheKey;
                    return response($response);
                }
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
                    $page->setTitle("?Heading", $pageOptions["Heading"]);
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

                
                if (array_key_exists("Title", $pageOptions) && $pageOptions["Title"]) {
                    $page->setTitle("Title", $pageOptions["Title"]);
                }
                if (array_key_exists("TopicID", $pageOptions) && $pageOptions["TopicID"]) {
                    $page->setText("TopicID", $pageOptions["TopicID"]);
                }
                for ($i = 3; $i<=30; $i++) {
                    if (array_key_exists("BCV" . strval($i), $pageOptions) && $pageOptions["BCV" . strval($i)]) {
                        $page->setText("BCV" . strval($i), $pageOptions["BCV" . strval($i)]);
                    }
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
        $type = $request->type;

        if (isset($type) && $type == 'delete_content') {
            // $blocks = Notion::block($pageId)->children()->asCollection()->toArray();
            $blocks = $this->getAllBlocks($pageId);
            try {
                foreach($blocks as $block) {
                    $path = "https://api.notion.com/v1/blocks/" . $block->getId();
                    $result = $this->curl_del($path);
                }
            }
            catch(Exception $e) {
                return response(['success' => false, 'message' => $e->getMessage()]);
            }
            return response(['success' => true]);
        }
        else {
            if ($property_value) {
                $page = new Page();
                $page->setId($pageId);
    
                switch($property_name) {
                    case "Heading":
                            $page->setTitle("?Heading", $property_value);
                        break;
    
                    case "Title":
                            $page->setTitle("Title", $property_value);
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
                    case "BCV3":
                    case "BCV4":
                    case "BCV5":
                    case "BCV6":
                    case "BCV7":
                    case "BCV8":
                    case "BCV9":
                    case "BCV10":
                    case "BCV11":
                    case "BCV12":
                    case "BCV13":
                    case "BCV14":
                    case "BCV15":
                    case "BCV16":
                    case "BCV17":
                    case "BCV18":
                    case "BCV19":
                    case "BCV20":
                    case "BCV21":
                    case "BCV22":
                    case "BCV23":
                    case "BCV24":
                    case "BCV25":
                    case "BCV26":
                    case "BCV27":
                    case "BCV28":
                    case "BCV29":
                    case "BCV30":
                    case "TopicID":
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

        // $blocks = Notion::block($blockId)
        //     ->children()
        //     ->asCollection()
        //     ->toArray();

        $blocks = $this->getAllBlocks($blockId);
        
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
                                // if ($blockId == "ce9e9bf1-110c-4822-9ad3-534ee2ee4844") {
                                //     dd($block->getRawContent());
                                // }
                                $syncedBlockId = $block->getRawContent()['synced_from']['block_id'];
                                $contents[$index] = $this->getBlockIncludingChilds($syncedBlockId);

                                if (count($contents[$index])) {
                                    $contents[$index] = $contents[$index][0];
                                }
                                else {
                                    // $contents[$index] = ["asdf"=>"asdf"];
                                }
                            }
                        }
                        break;

                    case "table_row":
                        $contents[$index]['id'] = $block->getId();
                        $contents[$index]['type'] = $block->getType();
                        $contents[$index]['data'] = $block->getRawContent()["cells"];
                        break;

                    case "paragraph":
                        $contents[$index]['id'] = $block->getId();
                        $contents[$index]['type'] = $block->getType();
                        $contents[$index]['plain_text'] = $block->asText();
                        $contents[$index]['styled_text'] = $this->getStyledContent($block->getRawContent());
                        break;

                    case "bulleted_list_item":
                        $contents[$index]['id'] = $block->getId();
                        $contents[$index]['type'] = $block->getType();
                        $contents[$index]['plain_text'] = $block->asText();
                        $contents[$index]['styled_text'] = $this->getStyledContent($block->getRawContent());
                        break;

                    case "table":
                        $contents[$index]['id'] = $block->getId();
                        $contents[$index]['type'] = $block->getType();
                        $contents[$index]['plain_text'] = $block->asText();
                        $contents[$index]['table_options'] = $block->getRawResponse()["table"];
                        break;

                    // case "table_row":
                    //     $contents[$index]['id'] = $block->getId();
                    //     $contents[$index]['type'] = $block->getType();
                    //     $contents[$index]['plain_text'] = $block->asText();
                    //     $contents[$index]['table_options'] = $block->getRawResponse()["table"];
                    //     break;

                    // case "table_of_contents":
                    //     dd("table_of_contents exists!!!");
                    //     break;

                    case "quote":
                        $contents[$index]['id'] = $block->getId();
                        $contents[$index]['type'] = $block->getType();
                        $contents[$index]['plain_text'] = $block->asText();

                        $rawContent = $block->getRawContent();
                        $contents[$index]['color'] = $rawContent["color"];

                        $styledContent = $this->getStyledContent($rawContent);
                        if ($styledContent && count($styledContent)) {
                            $contents[$index]['styled_text'] = $styledContent;
                        }
                        break;

                    case "image":
                        $contents[$index]['id'] = $block->getId();
                        $contents[$index]['type'] = $block->getType();
                        $contents[$index]['plain_text'] = $block->asText();
                        $rawContent = $block->getRawContent();
                        $contents[$index]['file'] = $rawContent['file'];
                        if (array_key_exists('caption', $rawContent)) {
                            $contents[$index]['caption'] = $rawContent['caption'];
                        }
                        break;

                    default: 
                        $contents[$index]['id'] = $block->getId();
                        $contents[$index]['type'] = $block->getType();
                        $contents[$index]['plain_text'] = $block->asText();
                        $styledContent = $this->getStyledContent($block->getRawContent());
                        if ($styledContent && count($styledContent)) {
                            $contents[$index]['styled_text'] = $styledContent;
                        }
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
        $idArray = array_map(function($subArray) {
            return $subArray['id'];
        }, $contents);
        $uniqueIds = array_unique($idArray);
        $uniqueSubArrays = array_intersect_key($contents, $uniqueIds);
        return array_values((array) $uniqueSubArrays);
    }


    /**
     * Get Styled texts/contents
     */
    private function getStyledContent($rawContents = null)
    {
        if ($rawContents && array_key_exists("text", $rawContents)) {
            $styledContent = [];
            for ($idx = 0; $idx < count($rawContents["text"]); $idx++) {
                array_push(
                    $styledContent,
                    // array(
                    //     "styles" => $rawContents["text"][$idx]["annotations"],
                    //     "plain_text" => $rawContents["text"][$idx]["plain_text"]
                    // )
                    $rawContents["text"][$idx]
                );
            }
            return $styledContent;
        }
        return [];
    }

    /**
     * 
     */
    private function processFilters($filter_options)
    {
        $filter_arr = [];

        return $filter_arr;
    }


    /**
     * Get All Blocks (recursive call due to page limit - 100)
     */
    private function getAllBlocks($blockId, $offset = null)
    {
        $blocks = Notion::block($blockId);

        if (!is_null($offset)) {
            $startCursor = new StartCursor($offset);
            $blocks = $blocks->offset($startCursor);
        }

        $blocks = $blocks->children()
            ->asCollection()
            ->toArray();
        
        if (count($blocks) == 100) {
            $next_page = $this->getAllBlocks($blockId, $blocks[99]->getId());
            $blocks = array_merge($blocks, $next_page);
        }

        return $blocks;
    }

    // /**
    //  * Delete All Childs (recursive call due to page limit - 100)
    //  */
    // private function deleteAllChilds($blockId, $offset = null)
    // {
    //     $parent = Notion::block($blockId);

    //     if (!is_null($offset)) {
    //         $startCursor = new StartCursor($offset);
    //         $parent = $parent->offset($startCursor);
    //     }

    //     $childBlocks = $parent->children()->asCollection()->toArray();

    //     if (count($parent) == 100) {
    //         $next_page
    //     }

    // }


    public function curl_del($path, $json = '')
    {
        $url = $path;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers = [
            'Authorization: Bearer ' . config('app.notion_token'),
            'Notion-Version: 2021-05-13',
        ];
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        $result = json_decode($result);
        curl_close($ch);

        return $result;
    }

    function getId($subArray) {
        return $subArray['id'];
    }

    /**
     * Generate JSON (endpoint for cron: once a day)
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function generateJSON() 
    {
        /**
         * 1st db (annotations)
         * */ 
        $dbId = "1c0177073ec846959efe002c9dd723e8";
        $records = $this->getNextPage($dbId);

        // dd($records);
        $bcvString = "";
        foreach ($records as $record) {
            if (count($record["properties"]["BCV1"]["rich_text"])) {
                // var_dump($record["properties"]["BCV1"]["rich_text"][0]["plain_text"]);
                $bcvString .= $record["properties"]["BCV1"]["rich_text"][0]["plain_text"];
            }
        }

        // $bcvString = str_replace("**", "*", $bcvString);
        // dd($bcvString);
        $path = 'assets/annotations.txt';
        Storage::disk('public')->put($path, $bcvString);

        /**
         * 2nd db (entries)
         * */ 
        $dbId = "2251110649bb4e80b04d65c24a2643f4";
        $records = $this->getNextPage($dbId);

        $bcvString = "";
        foreach ($records as $record) {
            if (count($record["properties"]["BCV1"]["rich_text"])) {
                // var_dump($record["properties"]["BCV1"]["rich_text"][0]["plain_text"]);
                $bcvString .= $record["properties"]["BCV1"]["rich_text"][0]["plain_text"];
            }
        }

        $path = 'assets/entries.txt';
        Storage::disk('public')->put($path, $bcvString);
    }

    /**
     * Recursive call to get all blocks from the database
     */
    private function getNextPage($dbId, $offset = null)
    {
        $blocks = Notion::database($dbId);

        if (!is_null($offset)) {
            $startCursor = new StartCursor($offset);
            $blocks = $blocks->offset($startCursor);
        }
        
        $blocks = $blocks->query()->getRawResponse();
        $blocks = $blocks["results"];
        
        if (count($blocks) == 100) {
            $next_page = $this->getNextPage($dbId, $blocks[99]["id"]);
            $blocks = array_merge($blocks, $next_page);
        }

        return $blocks;
    }
}
