<?php

namespace opensixt\UserAdminBundle\Controller;

class LanguageController extends AbstractController
{
    /**
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($page = 1)
    {
        $this->requireAdminUser();

        $query = $this->getLanguageRepository()
                      ->getQueryForAllLanguages();
        $pagination = $this->paginator->paginate($query, $page, 25);

        return $this->templating->renderResponse('opensixtUserAdminBundle:Language:list.html.twig',
                                                 array('pagination' => $pagination));
    }

    /**
     * @return \opensixt\BikiniTranslateBundle\Repository\LanguageRepository
     */
    private function getLanguageRepository()
    {
        return $this->em->getRepository('opensixtBikiniTranslateBundle:Language');
    }
}