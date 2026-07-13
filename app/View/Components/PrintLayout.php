<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PrintLayout extends Component
{
    public function __construct(
        public string $title = '',
        public string $size = 'A4',
        public bool $landscape = false,
        public ?string $printedBy = null,
        public string $margin = '12mm 10mm 14mm 10mm',
    ) {}

    public function render(): View
    {
        return view('print.layout', [
            'title' => $this->title,
        ]);
    }
}
