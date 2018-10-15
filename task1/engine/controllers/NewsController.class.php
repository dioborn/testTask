<?php

class NewsController
{
    public function getlist($options)
    {
        $App = Application::getInstance();

        $page = !empty($options['page']) && is_numeric($options['page']) ? $options['page'] : 1;
        $page = (int) $page;

        $categories = !empty($options['categories']) && is_array($options['categories']) ? $options['categories'] : array();

        $filters = array(
            'category_id' => $categories
        );

        $news = $App->news->GetList($page, $filters);
        $count = $App->news->GetCount($filters);

        echo json_encode(array(
            'status' => 'ok',
            'data' => array(
                'news' => $news ? $news : array(),
                'count' => $count
            )
        ));
    }

    public function add($options)
    {
        $App = Application::getInstance();

        $errors = array();

        // Check access level
        if ($App->user->GetLevel() < LEVEL_AUTHOR) {
            $errors[] = 'NO_ACCESS';
            $this->sendError($errors);
            return;
        }

        if (empty($options['title'])) {
            $errors[] = 'EMPTY_TITLE';
        } elseif (strlen($options['title']) < 3) {
            $errors[] = 'SHORT_TITLE';
        }
        if (empty($options['text'])) {
            $errors[] = 'EMPTY_TEXT';
        } elseif (strlen($options['text']) < 3) {
            $errors[] = 'SHORT_TEXT';
        }
        if (empty($options['category'])) {
            $errors[] = 'EMPTY_CATEGORY';
        } elseif (!$this->isValidCategory($options['category'])) {
            $errors[] = 'INVALID_CATEGORY';
        }

        if ($errors) {
            $this->sendError($errors);
            return;
        }


        $id = $App->news->Add(array(
            'title' => $options['title'],
            'text' => $options['text'],
            'category_id' => (int) $options['category'],
            'user_id' => $App->user->GetId()
        ));

        if(!$id) {
            $this->sendError(['ERROR_DB']);
            return;
        }
        
        $news = $App->news->GetById($id);

        echo json_encode(array(
            'status' => 'ok',
            'data' => $news
        ));
    }

    private function isValidCategory($category)
    {
        $App = Application::getInstance();

        $categories = $App->categories->GetList();

        foreach ($categories as $row) {
            if ($row['id'] == $category) {
                return true;
            }
        }

        return false;
    }

    private function sendError($errors)
    {
        echo json_encode(array(
            'status' => 'error',
            'codes' => $errors
        ));
    }

}
