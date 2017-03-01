<?php


namespace Openstore\Model\Util;

/**
 * Class ProductSearchableReference.
 */
class ProductSearchableReference
{
    /**
     * @var array
     */
    protected $params;

    /**
     * ProductSearchableReference constructor.
     *
     *
     * @param array|null $params
     */
    public function __construct(array $params = [])
    {
        $this->params = array_merge($this->getDefaultParams(), $params);
    }

    /**
     * Return default params.
     *
     * @return array
     */
    protected function getDefaultParams()
    {
        $params = [
            'ref_min_length' => 1,
            'ref_max_length' => 20,
            'ref_validation_regexp' => '/^%?([A-Za-z0-9)([A-Za-z0-9\ \_\-\/\*])+$/',
            // When using the method getReferenceSqlSearch
            // the wildcards will be placed after every chars
            // after x positions
            'sql_wildcards_starts_at_char' => 2
        ];

        return $params;
    }

    /**
     * Return reference search pattern that can be used for
     * sql like predicate.
     *
     * Return a '%' is nothing matches
     *
     * @param string $reference
     *
     * @return string
     */
    public function getReferenceSqlSearch($reference)
    {
        $wildcard_starts_at_char = $this->params['sql_wildcards_starts_at_char'];

        $search = '';
        $references = $this->findReferencesInText($reference);
        if (count($references) > 0) {
            // Make the first possible reference searchable
            $searchable = $this->createSearchableReference($references[0]);

            // Apply wildcard for a like clause
            foreach (str_split($searchable) as $idx => $c) {
                if ($idx >= $wildcard_starts_at_char) {
                    $search .= '%';
                }
                $search .= $c;
            }
        }

        $search .= '%';

        return $search;
    }

    /**
     * Try to grep occurences of a possible product reference in a searchText.
     *
     * @param string $searchText
     *
     * @return array
     */
    public function findReferencesInText($searchText)
    {
        $matches = [];

        // Step 1: test if the text could be a reference
        if ($this->isValidReference($searchText)) {
            $matches = [$searchText];
        } else {
            $words = explode(' ', $searchText);
            foreach ($words as $word) {
                if ($this->isValidReference($word)) {
                    $matches[] = $word;
                }
            }
        }

        return $matches;
    }

    /**
     * Test whether the entered text is a valid sku reference according
     * to the sku pattern.
     *
     * Set $check_minimum length to false if you want to check for partial references
     *
     * @param string $reference
     * @param bool   $check_minimum_length
     *
     * @return bool
     */
    public function isValidReference($text, $check_minimum_length = true)
    {
        $length = strlen($text);
        $valid = false;
        if ($length <= $this->params['ref_max_length']) {
            if (!($check_minimum_length && $length < $this->params['ref_min_length'])) {
                $valid = (bool) preg_match($this->params['ref_validation_regexp'], $text);
            }
        }

        return $valid;
    }

    /**
     * Return quoted searchable reference from a keyword.
     *
     * This method should mimic the behaviour of the mysql function
     * present in openstore-schema-core : get_searchable_reference()
     *
     * @param string $reference
     *
     * @return string
     */
    public function createSearchableReference($reference)
    {
        $reference = strtoupper(substr($reference, 0, $this->params['ref_max_length']));

        // Step 1:
        //  - strip any non ascii characters and
        //    and special chars like _-/...
        $ref = preg_replace('/[^A-Z0-9]/', '', $reference);

        // Step 2:
        //  - remove non significative leading zeros
        //    they are generally forgotten when talking
        $ref = preg_replace('/(0)+([1-9])/', '\\2', $ref);

        return $ref;
    }
}
