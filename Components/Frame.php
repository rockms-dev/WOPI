<?php

namespace MS\Wopi\Components;

use Illuminate\View\Component;
use Illuminate\Support\Str;
use MS\Wopi\Contracts\AbstractDocumentManager;

class   Frame extends Component
{
    public $url;
    public $access_token;
    public $ttl;
    
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $document = app(AbstractDocumentManager::class)::find($id);

        $this->access_token = Str::random(15);
        $this->ttl = 0;
        $this->url = $document->getUrlForAction('view', [
            'ui' => 'en',
            'rs' => 'en',
        ]);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('wopi::components.wopi-frame');
    }
}
