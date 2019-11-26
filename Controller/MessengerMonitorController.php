<?php


namespace KaroIO\MessengerMonitorBundle\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessengerMonitorBundleController
{
    public function __construct()
    {
    }

    /**
     * @Route("/admin/messenger-monitor")
     */
    public function showDashboard()
    {
        return new Response('La');

    }

}
