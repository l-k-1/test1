<?php

namespace Corp\Http\Controllers;

use Corp\Category;
use Corp\Repositories\ArticlesRepository;
use Corp\Repositories\PortfoliosRepository;
use Corp\Repositories\CommentsRepository;
use Config;

use Illuminate\Http\Request;

class ArticlesController extends SiteController
{
    public function __construct(PortfoliosRepository $p_rep, ArticlesRepository $a_rep,CommentsRepository $c_rep)
    {
        parent::__construct(new \Corp\Repositories\MenusRepository(new \Corp\Menu));

        $this->p_rep = $p_rep;
        $this->a_rep = $a_rep;
        $this->c_rep = $c_rep;

        $this->bar = 'right';
        $this->template = env('THEME').'.articles';
    }
    public function index($cat_alias = FALSE)
    {
        //
        $articles = $this->getArticles($cat_alias);

        $content = view(env('THEME').'.articles_content')->with('articles',$articles)->render();
        $this->vars = array_add($this->vars,'content',$content);

        $comments = $this->getComments(config('settings.recent_comments'));
        $portfolios = $this->getPortfolios(config('settings.recent_portfolios'));

        $this->contentRightBar = view(env('THEME').'.articlesBar')->with(['comments' => $comments, 'portfolios' => $portfolios]);

        return $this->renderOutput();
    }

    protected function getArticles($alias = FALSE)
    {
        $where = FALSE;

        if($alias) {
            $id = Category::select('id')->where('alias',$alias)->first()->id;
            $where = ['category_id',$id];
        }
        $articles = $this->a_rep->get(['id','title','created_at','alias','img','desc','category_id','user_id'],FALSE,TRUE, $where);

        if ($articles){
            $articles->load('user','category','comments');
        }

        return $articles;
    }

    private function getComments($take) {
        $comments = $this->c_rep->get(['name','email','site','text','article_id','user_id'], $take);
        if ($comments){
            $comments->load('article','user');
        }
        return $comments;
    }

    private function getPortfolios($take) {
        return $this->p_rep->get(['title','text','img','customer','alias','filter_alias'], $take);
    }

    public function show($alias = FALSE)
    {
        $article = $this->a_rep->one($alias,['comments'=>TRUE]);

        dd($article);
        $content = view(env('THEME').'.article_content')->with('article',$article)->render();

        $this->vars = array_add($this->vars,'content',$content);

        $comments = $this->getComments(config('settings.recent_comments'));
        $portfolios = $this->getPortfolios(config('settings.recent_portfolios'));

        $this->contentRightBar = view(env('THEME').'.articlesBar')->with(['comments' => $comments, 'portfolios' => $portfolios]);

        return $this->renderOutput();
    }

}
