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
namespace MomMock\Controller\Backend;

use Slim\Http\Request;
use Slim\Http\Response;
use MomMock\Entity\Aggregate;
use MomMock\Entity\Source;

/**
 * Class AggregateController
 * @package MomMock\Controller\Backend
 * @author  Mahmood Dhia <m.dhia@techdivision.com>
 */
class AggregateController extends AbstractBackendController
{
    /**
     * Aggregate list action
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function indexAction(Request $request, Response $response)
    {
        $db = $this->getDb();

        $aggregates = $db->createQueryBuilder()
            ->select('*')
            ->from('`' . Aggregate::TABLE_NAME . '`')
            ->execute()
            ->fetchAll();

        $aggregates = $this->addSources($aggregates);

        $templ = $this->getTemplateEngine();

        $response->write($templ->render(
            'aggregate/index.twig',
            ['aggregates' => $aggregates]
        ));

        return $response;
    }

    /**
     * Aggregate detail action
     *
     * @param Request $request
     * @param Response $response
     * @param $params
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function detailAction(Request $request, Response $response, $params)
    {
        $id = 0;
        if (isset($params['id'])) {
            $id = $params['id'];
        }

        $aggregate = $this->getDb()->createQueryBuilder()
            ->select('*')
            ->from('`' . Aggregate::TABLE_NAME . '`')
            ->where('`id` = ?')
            ->setParameter(0, $id)
            ->execute()
            ->fetch();

        $aggregate = $this->addSources(['aggregate' => $aggregate]);

        $templ = $this->getTemplateEngine();

        $response->write($templ->render(
            'aggregate/detail.twig',
            $aggregate
        ));

        return $response;
    }

    /**
     * Aggregate add action
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function addAction(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        if (is_null($data)) {
            $response->write($this->getTemplateEngine()->render(
                'aggregate/add.twig'
            ));
        } else {
            $this->saveAggregate(array_map('trim', $data));
            $response = $response->withRedirect('/aggregate');
        }

        return $response;
    }

    /**
     * Aggregate delete action
     *
     * @param Request $request
     * @param Response $response
     * @param $params
     * @return Response
     */
    public function deleteAction(Request $request, Response $response, $params)
    {
        if (isset($params['id'])) {
            $aggregate = new Aggregate($this->getDb());
            $aggregate->delete($params['id']);
        }

        return $response->withRedirect('/aggregate');
    }

    /**
     * Add source information to aggregate
     *
     * @param $aggregates
     * @return mixed
     */
    private function addSources($aggregates)
    {
        foreach ($aggregates as &$aggregate) {
            $sources = $this->getDb()->createQueryBuilder()
                ->select('*')
                ->from('`' . Source::TABLE_NAME . '`')
                ->where('`aggregate_id` = ?')
                ->setParameter(0, $aggregate['id'])
                ->execute()
                ->fetchAll();

            $aggregate['sources'] = $sources;
        }

        return $aggregates;
    }

    /**
     * Saves a new aggregate and its sources
     *
     * @param $data
     */
    private function saveAggregate($data)
    {
        $aggregate = new Aggregate($this->getDb());
        $aggregateId = $aggregate->setData(['name' => $data['name']])->save();

        foreach (explode(',', $data['sources']) as $sourceData) {
            $sourceData = trim($sourceData);
            $source = new Source($this->getDb());
            $source->setData(['source_id' => $sourceData, 'aggregate_id' => $aggregateId])->save();
        }
    }
}