<?php

namespace App\Tests;

//use App\Services\RequestService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

trait EMTestTrait
{
    protected ?EntityManagerInterface $em = null;
    protected RequestStack $stack;
    /*protected ProjectContextService $projectContextService;
    protected RequestService $requestService;*/

    private function initTrait()
    {
        $this->getEntityManager();

        /** @var RequestStack $stack */
        $stack = self::$container->get("request_stack");
        $this->stack = $stack;

        /*$requestService = self::$container->get(RequestService::class);
        $this->requestService = $requestService;*/
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        if ($this->em === null) {
            if (self::$container === null) {
                die("Container not yet initialized, can't get EntityManager");
            }
            /** @var \Doctrine\Persistence\ManagerRegistry $registry */
            $registry = self::$container->get('doctrine');
            /** @var EntityManagerInterface $em */
            $em = $registry->getManager();
            $this->em = $em;
        }
        return $this->em;
    }

    private function releaseTrait()
    {
        // avoid memory leaks
        $this->em->close();
        unset($this->em);
        unset($this->stack);
        gc_collect_cycles();
    }

    protected function refreshEntity(&$object): void
    {
        $object = $this->em->getRepository(get_class($object))->find($object->getId());
    }
}
