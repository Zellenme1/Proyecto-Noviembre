<?php
require_once __DIR__ . '/Repo.php';

class Controller
{
    private Repo $repo;

    public function __construct()
    {
        $this->repo = new Repo();
    }

    public function pintalaa()
    {
        echo $this->repo->getMensajeA();
    }

    public function pintalab()
    {
        echo $this->repo->getMensajeB();
    }
}
