<?php
/**
 * Copyright (c) 2018 Magenerds
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
use MomMock\Entity\Journal\Request as JournalRequest;

/**
 * Class JournalController
 * @package MomMock\Controller\Backend
 * @author  Mahmood Dhia <m.dhia@techdivision.com>
 */
class JournalController extends AbstractBackendController
{
    /**
     * Order list action
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

        $requests = $db->createQueryBuilder()
            ->select('*')
            ->from('`journal`')
            ->execute()
            ->fetchAll();

        $templ = $this->getTemplateEngine();

        $response->write($templ->render(
            'journal/index.twig',
            ['requests' => $requests]
        ));

        return $response;
    }

    public function detailAction(Request $request, Response $response, $params)
    {
        $id = 0;
        if (isset($params['id'])) {
            $id = $params['id'];
        }

        $templ = $this->getTemplateEngine();

        $response->write($templ->render(
            'journal/detail.twig',
            $this->getJournalDetails($id)
        ));

        return $response;
    }

    /**
     * Get journal details by id.
     *
     * @param int|string $id
     * @return array[]
     */
    private function getJournalDetails($id): array
    {
        $db = $this->getDb();
        $journal = $db->createQueryBuilder()
            ->select('*')
            ->from('`' . JournalRequest::TABLE_NAME . '`')
            ->where('`id` = ?')
            ->setParameter(0, $id)
            ->execute()
            ->fetch();

        return ['journal' => $journal];
    }
}