<?php

namespace opensixt\UserAdminBundle\Controller;

class GroupController extends AbstractController
{
    /**
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($page)
    {
        $this->requireAdminUser();

        $query = $this->getGroupRepository()
                      ->getQueryForAllGroups();
        $pagination = $this->paginator->paginate($query, $page, 25);

        return $this->templating->renderResponse('opensixtUserAdminBundle:Group:list.html.twig',
                                                 array('pagination' => $pagination));
    }

    /**
     * @return \opensixt\BikiniTranslateBundle\Repository\GroupsRepository
     */
    private function getGroupRepository()
    {
        return $this->em->getRepository('opensixtBikiniTranslateBundle:Groups');
    }
}