<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Collection;
use FiveamCode\LaravelNotionApi\Notion;
use FiveamCode\LaravelNotionApi\Query\Filters\Filter;
use FiveamCode\LaravelNotionApi\Query\Sorting;
use FiveamCode\LaravelNotionApi\Endpoints\Database;
// use FiveamCode\LaravelNotionApi\Endpoints\Database;
// use FiveamCode\LaravelNotionApi\Entities\Collections\PageCollection;
// use FiveamCode\LaravelNotionApi\Entities\Page;

class IndexController extends Controller
{
    //
    public function index()
    {
        $yourDatabaseId = "b36c1bbe9ec04799b12fd7b7d2f727aa";
        $yourPageId = "b046b70a46a74c72bf0e70f470dcd1a9";

        $blockID = "c6e66d69-bbe0-473e-b7d3-75effde10d2e";
        // $blockID = "1b1d700365bb4fb6980e4605abc035d0";

        $notion = new Notion("secret_uSs7fTGZXxIz2OGqklQnc8ACCxFP9iVraeroXI0Laao");
        $db = $notion->databases()->find($yourDatabaseId)->title;
        dd($db);

        // $page = $notion->pages()->find($yourPageId);
        
        // $page = $notion->block($yourPageId)->children();
        // dd($page);

        $block = $notion->block($blockID)->children();
        // dd($block);
        

        $sortings = new Collection();
        $filters = new Collection();

        // $sortings
        // ->add(Sorting::propertySort('Ordered', 'ascending'));
        // $sortings
        // ->add(Sorting::timestampSort('created_time', 'ascending'));

        // $filters->add(Filter::textFilter('title', ['contains' => 'must']));
        // or
        $filters->add(Filter::rawFilter('properties', ['Heading' => ['contains' => 'must']]));
        
        $collections = $notion->database($yourDatabaseId)->filterBy($filters)->query();
        dd($collections);
        $collections = $notion->database($yourDatabaseId)
            ->filterBy($filters) // filters are optional
            // ->sortBy($sortings) // sorts are optional
            ->limit(5) // limit is optional
            ->query()
            ->asCollection();
        dd($collections);

        // // $notion->databases()->find("b36c1bbe9ec04799b12fd7b7d2f727aa");
        // $notion->pages()->find("1b1d700365bb4fb6980e4605abc035d0");

        // $sortings = new Collection();
        // $filters = new Collection();

        // $filters->add(Filter::textFilter("title", ["contains" => "new"]));
        // // or
        // $filters->add(Filter::rawFilter("Tags", ["multi_select" => ["contains" => "great"]]));

        // dd($page);

        
        dd('exit');
        // dd($notion->filterBy(new));
        return view('welcome', compact('notion'));
    }
}
