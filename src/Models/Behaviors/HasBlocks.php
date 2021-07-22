<?php

namespace A17\Twill\Models\Behaviors;

use A17\Twill\Models\Block;
use Carbon\Carbon;

trait HasBlocks
{
    public function blocks()
    {
        return $this->morphMany(Block::class, 'blockable')->orderBy(config('twill.blocks_table', 'twill_blocks') . '.position', 'asc');
    }

    public function renderBlocks($renderChilds = true, $blockViewMappings = [], $data = [])
    {
        return $this->blocks->where('parent_id', null)->map(function ($block) use ($blockViewMappings, $renderChilds, $data) {
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

    private function getBlockView($blockType, $blockViewMappings = [])
    {
        $view = config('twill.block_editor.block_views_path') . '.' . $blockType;

        if (array_key_exists($blockType, $blockViewMappings)) {
            $view = $blockViewMappings[$blockType];
        }

        return $view;
    }
}
