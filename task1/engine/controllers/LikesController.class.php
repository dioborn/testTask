<?php

class LikesController
{
    public function getlist($options)
    {
        $App = Application::getInstance();

        if (empty($options['id']) || !is_numeric($options['id'])) {
            $this->sendError('INCORRECT_ID');
        }

        $id = (int) $options['id'];
        $page = !empty($options['page']) && is_numeric($options['page']) ? $options['page'] : 1;
        $page = (int) $page;

        $likes = $App->likes->GetList($id, $page);

        echo json_encode(array(
            'status' => 'ok',
            'data' => $likes
        ));
    }

    public function like($options)
    {
        $App = Application::getInstance();

        $id = $App->likes->Add(array(
            'news_id' => (int) $options['id'],
            'user_id' => $App->user->GetId()
        ));

        if (!$id) {
            $this->sendError(['ERROR_DB']);
            return;
        }

        $count = $App->likes->GetCount($options['id']);

        echo json_encode(array(
            'status' => 'ok',
            'data' => $count
        ));
    }

    public function unlike($options)
    {
        $App = Application::getInstance();

        $deleted = $App->likes->Delete(array(
            'news_id' => (int) $options['id'],
            'user_id' => $App->user->GetId()
        ));

        if (!$deleted) {
            $this->sendError(['ERROR_DB']);
            return;
        }

        $count = $App->likes->GetCount($options['id']);

        echo json_encode(array(
            'status' => 'ok',
            'data' => $count
        ));
    }

    private function sendError($errors)
    {
        if (is_string($errors)) {
            $errors = array($errors);
        }

        echo json_encode(array(
            'status' => 'error',
            'codes' => $errors
        ));
    }

}
