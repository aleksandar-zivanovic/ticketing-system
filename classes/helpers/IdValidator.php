<?php

class IdValidator
{
    /**
     * Validate and prepare IDs for placeholders and binding in SQL query.
     * 
     * @param array $ids Array of IDs to be validated and prepared.
     * @return array An array containing the integer IDs and their respective placeholders for SQL.
     * @throws Exception If any of the IDs are invalid.
     */
    public static function prepareIdsForQuery(array $ids): array
    {
        $integerIds = [];
        $params = [];
        foreach ($ids as $key => $value) {
            if (is_numeric($value) && $value > 0) {
                $integerIds[] = intval($value);
                $params[] = ":id{$key}";
            } else {
                throw new Exception("ID isn't valid");
            }
        }

        return [$integerIds, $params];
    }
}
