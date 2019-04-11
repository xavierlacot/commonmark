<?php

/*
 * This file is part of the league/commonmark-ext-smartpunct package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (http://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Ext\SmartPunct;

use League\CommonMark\Block\Element\Document;
use League\CommonMark\Block\Element\Paragraph;
use League\CommonMark\Block\Renderer as CoreBlockRenderer;
use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Renderer as CoreInlineRenderer;

class SmartPunctExtension implements ExtensionInterface
{
    public function register(ConfigurableEnvironmentInterface $environment)
    {
        $environment
            ->addInlineParser(new QuoteParser(), 10)
            ->addInlineParser(new PunctuationParser(), 0)

            ->addInlineProcessor(new QuoteProcessor(), 10)

            ->addBlockRenderer(Document::class, new CoreBlockRenderer\DocumentRenderer(), 0)
            ->addBlockRenderer(Paragraph::class, new CoreBlockRenderer\ParagraphRenderer(), 0)

            ->addInlineRenderer(Text::class, new CoreInlineRenderer\TextRenderer(), 0)
        ;
    }
}
