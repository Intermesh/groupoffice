<?php

declare(strict_types=1);

namespace SPFLib\Macro;

use SPFLib\Macro\MacroString\Chunk;

/**
 * Class that represents a macro-string value.
 */
class MacroString
{
    /**
     * @var \SPFLib\Macro\MacroString\Chunk[]
     */
    private $chunks;

    /**
     * Initialize the instance.
     *
     * @param \SPFLib\Macro\MacroString\Chunk[] $chunks
     */
    public function __construct(array $chunks = [])
    {
        $this->setChunks($chunks);
    }

    /**
     * Get the textual representation of this MacroString (to be used in the DNS TXT record).
     */
    public function __toString(): string
    {
        return implode('', array_map('strval', $this->getChunks()));
    }

    public function __clone()
    {
        $chunks = $this->getChunks();
        $this->setChunks([]);
        foreach ($chunks as $chunk) {
            $this->addChunk(clone $chunk);
        }
    }

    /**
     * Replace the chunks.
     *
     * @param \SPFLib\Macro\MacroString\Chunk[] $chunks
     *
     * @return $this
     */
    public function setChunks(array $chunks): self
    {
        $this->chunks = [];
        foreach ($chunks as $chunk) {
            $this->addChunk($chunk);
        }

        return $this;
    }

    /**
     * Get the parts that compose this MacroString.
     *
     * @return \SPFLib\Macro\MacroString\Chunk[]
     */
    public function getChunks(): array
    {
        return $this->chunks;
    }

    /**
     * Append a new chunk.
     *
     * @return $this
     */
    public function addChunk(Chunk $chunk): self
    {
        $this->chunks[] = $chunk;

        return $this;
    }

    /**
     * Check if this instance has one or more chunk.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->getChunks() === [];
    }

    /**
     * Check if this instance contains some placeholders.
     *
     * @return bool
     */
    public function containsPlaceholders(): bool
    {
        foreach ($this->getChunks() as $chunk) {
            if ($chunk instanceof Chunk\Placeholder) {
                return true;
            }
        }

        return false;
    }
}
