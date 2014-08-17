<?php

namespace MtHaml\Indentation;

interface IndentationInterface
{
    /**
     * Transitions to new indentation level
     *
     * @return IndentationInterface
     */
    public function newLevel($indent);

    /**
     * Returns the indentation char
     *
     * @return string|null
     */
    public function getChar();

    /**
     * Returns the indentation width
     *
     * @return int|null
     */
    public function getWidth();

    /**
     * Returns the indentation level
     *
     * @return int
     */
    public function getLevel();

    /**
     * Returns the indentation string for the current line
     *
     * Returns the string that should be used for indentation in regard to the
     * current indentation state.
     *
     * @param  int    $levelOffset Identation level offset
     * @param  string $fallback    Fallback indent string. If there is
     *                             currently no indentation level and
     *                             fallback is not null, the first char of
     *                             $fallback is returned instead
     * @return string A string of zero or more spaces or tabs
     */
    public function getString($levelOffset = 0, $fallback = null);
}

