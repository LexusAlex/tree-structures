<?php

declare(strict_types=1);

namespace Tree;

final class AdjacencyList
{
    /**
     * Выходной массив.
     * @var array|string[]
     */
    public array $output;
    /**
     * Входной массив.
     * @var array|string[]
     */
    private array $input;
    /**
     * Количество элементов входного массива.
     */
    private int $inputCount;
    /**
     * Ключ корневого элемента.
     * @var int|string
     */
    private string|int $keyRoot;
    /**
     * Временный массив.
     * @var array|string[]
     */
    private array $inputTemporary = [];
    /**
     * Псевдонимы ключей.
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

    /**
     * Итоговое дерево.
     */
    public function getTree(): array
    {
        /**
         * @psalm-suppress MixedArrayAccess
         */
        $this->getRootNodes();
        $this->recursiveSearchNodes($this->output);

        return $this->output;
    }

    /**
     * Корневые элементы.
     */
    public function getRootNodes(): array
    {
        $count = $this->inputCount;

        $this->inputTemporary = $this->input;

        // Если входной массив пустой, то цикл никогда не будет выполнен
        while ($count--) {
            /**
             * @psalm-suppress MixedArrayOffset
             * @psalm-suppress MixedArrayAccess
             * @var string $parent_id
             */
            // [9]['parent']
            $parent_id = $this->inputTemporary[$count][$this->aliases['parent']];

            if ($parent_id === $this->keyRoot) {
                $this->output[] = $this->inputTemporary[$count];
                unset($this->inputTemporary[$count]); // Удаляем родительские узлы
            }
        }

        return $this->output;
    }

    private function recursiveSearchNodes(array &$output): array
    {
        $aliases = $this->aliases;
        $outputCount = \count($output);

        while ($outputCount--) {
            $inputCount = $this->inputCount;

            while ($inputCount--) {
                if (\array_key_exists($inputCount, $this->inputTemporary)) {
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
                        if (!\array_key_exists($aliases['children'], $output[$outputCount])) {
                            $output[$outputCount][$aliases['children']] = [];
                        }
                        /*if (!isset($output[$outputCount][$aliases['children']])) {
                            $output[$outputCount][$aliases['children']] = [];
                        }*/
                        /**
                         * @psalm-suppress MixedAssignment
                         * @psalm-suppress MixedArrayAssignment
                         */
                        $output[$outputCount][$aliases['children']][] = $this->inputTemporary[$inputCount];

                        unset($this->inputTemporary[$inputCount]);
                        /**
                         * @psalm-suppress MixedArgument
                         */
                        return $this->recursiveSearchNodes($output[$outputCount][$aliases['children']]);
                    }
                }
            }
        }
        return [];
    }
}
