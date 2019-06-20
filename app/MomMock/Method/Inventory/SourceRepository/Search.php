<?php
/**
 * Copyright (c) 2019 Magenerds
 * All rights reserved
 *
 * This product includes proprietary software developed at Magenerds, Germany
 * For more information see http://www.magenerds.com/
 *
 * To obtain a valid license for using this software please contact us at
 * info@magenerds.com
 */

namespace MomMock\Method\Inventory\SourceRepository;

use MomMock\Method\AbstractIncomingMethod;
use MomMock\Entity\Source;
use MomMock\Entity\Aggregate;

/**
 * Class Search
 * @package MomMock\Method\Inventory\SourceRepository
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class Search extends AbstractIncomingMethod
{
    /**
     * @inheritdoc
     */
    public function handleRequestData($data)
    {
        $sourceData = $this->getSourceData();

        $method = $this->methodResolver->getMethodForServiceClass(get_class($this));
        $template = $this->templateHelper->getTemplateForMethod($method);

        $search = json_decode($template, true);

        $items = [];
        foreach ($sourceData as $source) {
            $sourceTemplate = json_encode($search['items'], true);

            $sourceTemplate = str_replace('{{source_id}}', $source['source_id'], $sourceTemplate);
            $sourceTemplate = str_replace('{{aggregate_id}}', $source['aggregate_id'], $sourceTemplate);

            $items = array_merge($items, json_decode($sourceTemplate, true));
        }

        $search['items'] = $items;

        return $search;
    }

    /**
     * Returns all source data
     *
     * @return []
     */
    protected function getSourceData()
    {
        $sources = $this->getDb()->createQueryBuilder()
            ->select('*')
            ->from('`' . Source::TABLE_NAME . '`', 's')
            ->leftJoin('s', '`' . Aggregate::TABLE_NAME . '`', 'a', 'a.id = s.aggregate_id')
            ->execute()
            ->fetchAll();

        return $sources;
    }
}