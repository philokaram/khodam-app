<?php
class ServantImportController
{
    public function index(): void
    {
        requireLogin();
        requirePermission('servants.create');

        view('servants/import', [
            'title'  => 'استيراد الخدام من Excel',
            'choirs' => (new Choir())->all(),
        ]);
    }
}