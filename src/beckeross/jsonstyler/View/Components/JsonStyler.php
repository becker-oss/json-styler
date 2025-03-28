<?php

namespace beckeross\jsonstyler\View\Components;

use Illuminate\View\Component;
use beckeross\jsonstyler\Repositories\JsonStylerRepository;

class JsonStyler extends Component
{
    public array|string $data;
    public string $theme;
    public JsonStylerRepository $styler;

    public function __construct(string $data, string $theme = 'default')
    {
        $this->styler = app(JsonStylerRepository::class);
        $this->data = $data;
        $this->theme = $theme;

        $this->styler->loadThemes(Config('jsonstyler'));

        $this->styler->setTheme($this->theme);
    }

    public function render()
    {
        return view('jsonstyler::components.json-styler', [
            'formattedJson' => $this->styler->formatJson($this->data),
            'themeConfig' => $this->styler->getTheme(),
        ]);

    }

}
