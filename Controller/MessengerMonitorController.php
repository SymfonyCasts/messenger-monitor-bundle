<?php


namespace KaroIO\MessengerMonitor\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessengerMonitorController
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
