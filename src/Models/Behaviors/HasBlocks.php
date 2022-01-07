<?php

namespace A17\Twill\Models\Behaviors;

use A17\Twill\Models\Block;
use Carbon\Carbon;

trait HasBlocks
{
    /**
     * Defines the one-to-many relationship for block objects.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function blocks()
    {
        return $this->morphMany(Block::class, 'blockable')->orderBy(config('twill.blocks_table', 'twill_blocks') . '.position', 'asc');
    }

    public function renderNamedBlocks($name = 'default', $renderChilds = true, $blockViewMappings = [], $data = [])
    {
        return $this->blocks
            ->filter(function ($block) use ($name) {
                return $name === 'default'
                ? ($block->editor_name === $name || $block->editor_name === null)
                : $block->editor_name === $name;
            })
            ->where('parent_id', null)
            ->map(function ($block) use ($blockViewMappings, $renderChilds, $data) {
                //Check if publication date
                if (!empty($block->publication['content'][config('app.locale')])) {
                    $publicationDate = Carbon::make($block->publication['content'][config('app.locale')]);
                    //Check if the block can be visible
                    if ($publicationDate->greaterThan(Carbon::now())) {
                        return false;
                    }
                }
                //Check if block is available for current locale
                if (!empty($block->locales['value'])) {
                    //Check if the block can be visible
                    if (!in_array(config('app.locale'), $block->locales['value'])) {
                        return false;
                    }
                }
                if ($renderChilds) {
                    $childBlocks = $this->blocks->where('parent_id', $block->id);

                    $renderedChildViews = $childBlocks->map(function ($childBlock) use ($blockViewMappings, $data) {
                        $view = $this->getBlockView($childBlock->type, $blockViewMappings);
                        return view($view, $data)->with('block', $childBlock)->render();
                    })->implode('');
                }

                $block->childs = $this->blocks->where('parent_id', $block->id);

                $view = $this->getBlockView($block->type, $blockViewMappings);

                return view($view, $data)->with('block', $block)->render() . ($renderedChildViews ?? '');
            })->implode('');
    }

    /**
     * Returns the rendered Blade views for all attached blocks in their proper order.
     *
     * @param bool $renderChilds Include all child blocks in the rendered output.
     * @param array $blockViewMappings Provide alternate Blade views for blocks. Format: `['block-type' => 'view.path']`.
     * @param array $data Provide extra data to Blade views.
     * @return string
     */
    public function renderBlocks($renderChilds = true, $blockViewMappings = [], $data = [])
    {
        return $this->renderNamedBlocks('default', $renderChilds, $blockViewMappings, $data);
    }

    private function getBlockView($blockType, $blockViewMappings = [])
    {
        $view = config('twill.block_editor.block_views_path') . '.' . $blockType;

        if (array_key_exists($blockType, $blockViewMappings)) {
            $view = $blockViewMappings[$blockType];
        }

        return $view;
    }
}
