<?php

declare(strict_types=1);

namespace WpjShop\GraphQL\Services;

use GraphQL\Client;
use GraphQL\Exception\QueryError;
use GraphQL\Mutation;
use GraphQL\Query;
use WpjShop\GraphQL\Exception\MethodNotImplementedException;

abstract class AbstractService implements ServiceInterface
{
    protected Client $client;

    private array $selection = [];

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Overwrites default selection.
     */
    public function setSelection(array $selection): self
    {
        $this->selection = $this->recursivelyCreateSelectionSet($selection);

        return $this;
    }

    protected function getSelection(): array
    {
        return $this->selection ?: $this->getDefaultSelectionSet();
    }

    /**
     * Adds selection to current selection.
     */
    public function addSelection(array $selection): self
    {
        $this->selection = array_merge(
            $this->getSelection(),
            $this->recursivelyCreateSelectionSet($selection)
        );

        return $this;
    }

    public function translate(int $id, string $language, array $data): array
    {
        throw new MethodNotImplementedException('Translate method is not implemented');
    }

    abstract protected function getDefaultSelectionSet(): array;

    protected function getSelectionSet(): array
    {
        return $this->createSelectionSet(
            $this->getSelection()
        );
    }

    protected function executeQuery(Query $gql, array $variables = [], bool $resetResult = true): ?array
    {
        try {
            $result = $this->client
                ->runQuery($gql, true, $variables)
                ->getData();

            if ($resetResult && is_array($result)) {
                return reset($result);
            }

            return $result;
        } catch (QueryError $e) {
            // mutation should always throw 404 errors
            if (!($gql instanceof Mutation)) {
                // handle not found error and return null as result
                if (($e->getErrorDetails()['extensions']['category'] ?? null) === 'NOT_FOUND') {
                    return null;
                }
            }

            // other errors should be thrown
            throw $e;
        }
    }

    protected function createBaseQuery(string $queryName, bool $list = false): Query
    {
        $selectionSet = $this->getSelectionSet();
        if ($list) {
            $selectionSet = [
                (new Query('items'))
                    ->setSelectionSet($this->getSelectionSet()),
                'hasNextPage',
                'hasPreviousPage',
            ];
        }

        return (new Query($queryName))
            ->setSelectionSet($selectionSet);
    }

    protected function createSelectionSet(array $selection): array
    {
        return $this->recursivelyCreateSelectionSet($selection);
    }

    private function recursivelyCreateSelectionSet(array $selection): array
    {
        $selectionSet = [];

        foreach ($selection as $key => $item) {
            if (is_array($item)) {
                $selectionSet[] = (new Query($key))
                    ->setSelectionSet($this->recursivelyCreateSelectionSet($item));

                continue;
            }

            $selectionSet[] = $item;
        }

        return $selectionSet;
    }
}
