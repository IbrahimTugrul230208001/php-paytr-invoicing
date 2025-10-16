<?php

namespace App\Controllers;

use App\Models\FaturaModel;
use App\Views\FaturaView;

class FaturalandırmaController
{
    protected $model;
    protected $view;

    public function __construct()
    {
        $this->model = new FaturaModel();
        $this->view = new FaturaView();
    }

    public function index()
    {
        // List all invoices
        $faturalar = $this->model->getAll();
        $this->view->renderList($faturalar);
    }

    public function create($data)
    {
        // Create a new invoice
        $result = $this->model->create($data);
        $this->view->renderCreateResult($result);
    }

    public function show($id)
    {
        // Show a single invoice
        $fatura = $this->model->getById($id);
        $this->view->renderDetail($fatura);
    }

    public function update($id, $data)
    {
        // Update an invoice
        $result = $this->model->update($id, $data);
        $this->view->renderUpdateResult($result);
    }

    public function delete($id)
    {
        // Delete an invoice
        $result = $this->model->delete($id);
        $this->view->renderDeleteResult($result);
    }

}
