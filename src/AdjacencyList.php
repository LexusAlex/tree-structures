<?php

declare(strict_types=1);

namespace Tree;

final class AdjacencyList
{
    /**
     * @var array|string[]
     */
    public array $output;
    /**
     * @var array|string[]
     */
    private array $input;

    private int $inputCount;
    /**
     * @var int|string
     */
    private string|int $keyRoot = 0;
    /**
     * @var array|string[]
     */
    private array $inputTemporary = [];
    /**
     * @var array|string[]
     */
    private array $aliases = [
        'id' => 'id',
        'parent' => 'parent',
        'title' => 'title',
        'children' => 'children',
        'position' => 'position',
    ];

    /**
     * AdjacencyList constructor.
     * @param array|string[] $input
     * @param array|string[]|null $aliases
     * @param int|string $keyRoot
     */
    public function __construct(array $input, array|null $aliases = null, string|int $keyRoot = 0)
    {
        $this->output = [];
        $this->input = $input;
        $this->inputCount = \count($input);
        $this->keyRoot = $keyRoot;
        //$this->sort_enabled = $sort_enabled;

        /** @psalm-suppress MixedArrayAccess */
        if ($aliases) {
            /**
             * @var string $value
             * @var string $alias
             */
            foreach ($aliases as $alias => $value) {
                if ($this->aliases[$alias]) {
                    $this->aliases[$alias] = $value;
                }
            }
        }
    }

    public function getTree(): array|string
    {
        /**
         * @psalm-suppress MixedArrayAccess
         */
        $this->makeRootNodes();
        $this->recursiveSearchNodes($this->output);

        return $this->output;
    }

    public function makeRootNodes(): void
    {
        $count = $this->inputCount;

        $this->inputTemporary = $this->input;

        while ($count--) {
            /**
             * @psalm-suppress MixedArrayOffset
             * @psalm-suppress MixedArrayAccess
             * @var string $parent_id
             */
            $parent_id = $this->inputTemporary[$count][$this->aliases['parent']];

            if ($parent_id === $this->keyRoot) {
                $this->output[] = $this->inputTemporary[$count];
                unset($this->inputTemporary[$count]); // Удаляем родительские узлы
            }
        }
    }

    private function recursiveSearchNodes(array &$output): void
    {
        $aliases = $this->aliases;
        $outputCount = \count($output);

        while ($outputCount--) {
            $inputCount = $this->inputCount;

            while ($inputCount--) {
                if (isset($this->inputTemporary[$inputCount])) {
                    /**
                     * @psalm-suppress MixedArrayOffset
                     * @psalm-suppress MixedArrayAccess
                     * @psalm-suppress MixedArrayTypeCoercion
                     */
                    if (
                        $output[$outputCount][$aliases['id']] ===
                        $this->inputTemporary[$inputCount][$aliases['parent']]
                    ) {
                        /**
                         * @psalm-suppress MixedArrayOffset
                         * @psalm-suppress MixedArrayAssignment
                         */
                        if (!isset($output[$outputCount][$aliases['children']])) {
                            $output[$outputCount][$aliases['children']] = [];
                        }
                        /**
                         * @psalm-suppress MixedAssignment
                         * @psalm-suppress MixedArrayAssignment
                         */
                        $output[$outputCount][$aliases['children']][] = $this->inputTemporary[$inputCount];

                        unset($this->inputTemporary[$inputCount]);
                        /**
                         * @psalm-suppress MixedArgument
                         */
                        $this->recursiveSearchNodes($output[$outputCount][$aliases['children']]);
                    }
                }
            }
        }
    }
}
