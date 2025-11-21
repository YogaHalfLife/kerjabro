<?php

namespace Illuminate\Pagination;

use Illuminate\Contracts\Pagination\LengthAwarePaginator as PaginatorContract;

class UrlWindow
{
    /**
     * The paginator implementation.
     *
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected $paginator;

    /**
     * Create a new URL window instance.
     *
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator  $paginator
     * @return void
     */
    public function __construct(PaginatorContract $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * Create a new URL window instance.
     *
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator  $paginator
     * @return array
     */
    public static function make(PaginatorContract $paginator)
    {
        return (new static($paginator))->get();
    }

    /**
     * Get the window of URLs to be shown.
     *
     * @return array
     */
    public function get()
    {
        $onEachSide = $this->paginator->onEachSide;

        if ($this->paginator->lastPage() < ($onEachSide * 2) + 8) {
            return $this->getSmallSlider();
        }

        return $this->getUrlSlider($onEachSide);
    }

    /**
     * Get the slider of URLs there are not enough pages to slide.
     *
     * @return array
     */
    protected function getSmallSlider()
    {
        return [
            'first' => $this->paginator->getUrlRange(1, $this->lastPage()),
            'slider' => null,
            'last' => null,
        ];
    }

    /**
     * Create a URL slider links.
     *
     * @param  int  $onEachSide
     * @return array
     */
    protected function getUrlSlider($onEachSide)
    {
        $window = $onEachSide + 4;

        if (! $this->hasPages()) {
            return ['first' => null, 'slider' => null, 'last' => null];
        }
        if ($this->currentPage() <= $window) {
            return $this->getSliderTooCloseToBeginning($window, $onEachSide);
        }
        elseif ($this->currentPage() > ($this->lastPage() - $window)) {
            return $this->getSliderTooCloseToEnding($window, $onEachSide);
        }
        return $this->getFullSlider($onEachSide);
    }

    /**
     * Get the slider of URLs when too close to the beginning of the window.
     *
     * @param  int  $window
     * @param  int  $onEachSide
     * @return array
     */
    protected function getSliderTooCloseToBeginning($window, $onEachSide)
    {
        return [
            'first' => $this->paginator->getUrlRange(1, $window + $onEachSide),
            'slider' => null,
            'last' => $this->getFinish(),
        ];
    }

    /**
     * Get the slider of URLs when too close to the ending of the window.
     *
     * @param  int  $window
     * @param  int  $onEachSide
     * @return array
     */
    protected function getSliderTooCloseToEnding($window, $onEachSide)
    {
        $last = $this->paginator->getUrlRange(
            $this->lastPage() - ($window + ($onEachSide - 1)),
            $this->lastPage()
        );

        return [
            'first' => $this->getStart(),
            'slider' => null,
            'last' => $last,
        ];
    }

    /**
     * Get the slider of URLs when a full slider can be made.
     *
     * @param  int  $onEachSide
     * @return array
     */
    protected function getFullSlider($onEachSide)
    {
        return [
            'first' => $this->getStart(),
            'slider' => $this->getAdjacentUrlRange($onEachSide),
            'last' => $this->getFinish(),
        ];
    }

    /**
     * Get the page range for the current page window.
     *
     * @param  int  $onEachSide
     * @return array
     */
    public function getAdjacentUrlRange($onEachSide)
    {
        return $this->paginator->getUrlRange(
            $this->currentPage() - $onEachSide,
            $this->currentPage() + $onEachSide
        );
    }

    /**
     * Get the starting URLs of a pagination slider.
     *
     * @return array
     */
    public function getStart()
    {
        return $this->paginator->getUrlRange(1, 2);
    }

    /**
     * Get the ending URLs of a pagination slider.
     *
     * @return array
     */
    public function getFinish()
    {
        return $this->paginator->getUrlRange(
            $this->lastPage() - 1,
            $this->lastPage()
        );
    }

    /**
     * Determine if the underlying paginator being presented has pages to show.
     *
     * @return bool
     */
    public function hasPages()
    {
        return $this->paginator->lastPage() > 1;
    }

    /**
     * Get the current page from the paginator.
     *
     * @return int
     */
    protected function currentPage()
    {
        return $this->paginator->currentPage();
    }

    /**
     * Get the last page from the paginator.
     *
     * @return int
     */
    protected function lastPage()
    {
        return $this->paginator->lastPage();
    }
}
