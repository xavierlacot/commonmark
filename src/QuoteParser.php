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

use League\CommonMark\Delimiter\Delimiter;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\InlineParserContext;
use League\CommonMark\Util\RegexHelper;

class QuoteParser implements InlineParserInterface
{
    protected $double = ['"', '“', '”'];
    protected $single = ["'", '‘', '’'];

    /**
     * @return string[]
     */
    public function getCharacters(): array
    {
        return array_merge($this->double, $this->single);
    }

    /**
     * @param InlineParserContext $inlineContext
     *
     * @return bool
     */
    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        $character = $this->getCharacterType($cursor->getCharacter());

        $charBefore = $cursor->peek(-1);
        if ($charBefore === null) {
            $charBefore = "\n";
        }

        $cursor->advance();

        $charAfter = $cursor->getCharacter();
        if ($charAfter === null) {
            $charAfter = "\n";
        }

        list($leftFlanking, $rightFlanking) = $this->determineFlanking($charBefore, $charAfter);
        $canOpen = $leftFlanking && !$rightFlanking;
        $canClose = $rightFlanking;

        $node = new Text($character, ['delim' => true]);
        $inlineContext->getContainer()->appendChild($node);

        // Add entry to stack to this opener
        $inlineContext->getDelimiterStack()->push(new Delimiter($character, 1, $node, $canOpen, $canClose));

        return true;
    }

    /**
     * @param string $character
     *
     * @return string|null
     */
    private function getCharacterType($character)
    {
        if (in_array($character, $this->double)) {
            return '“';
        } elseif (in_array($character, $this->single)) {
            return '’';
        }
    }

    /**
     * @param string $charBefore
     * @param string $charAfter
     *
     * @return string[]
     */
    private function determineFlanking($charBefore, $charAfter)
    {
        $afterIsWhitespace = preg_match('/\pZ|\s/u', $charAfter);
        $afterIsPunctuation = preg_match(RegexHelper::REGEX_PUNCTUATION, $charAfter);
        $beforeIsWhitespace = preg_match('/\pZ|\s/u', $charBefore);
        $beforeIsPunctuation = preg_match(RegexHelper::REGEX_PUNCTUATION, $charBefore);

        $leftFlanking = !$afterIsWhitespace &&
            !($afterIsPunctuation &&
                !$beforeIsWhitespace &&
                !$beforeIsPunctuation);

        $rightFlanking = !$beforeIsWhitespace &&
            !($beforeIsPunctuation &&
                !$afterIsWhitespace &&
                !$afterIsPunctuation);

        return [$leftFlanking, $rightFlanking];
    }
}
