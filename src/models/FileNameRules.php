<?php

namespace mc\models;

class FileNameRules {

    /**
     * @param string $type Тип шаблона
     * @param array $data Данные шаблона
     * @return string
     * @throws \Exception
     */
    public function getFileName($type, $data) {
        switch ($type) {
            case 'views':
            case 'docs':
            case 'controllers': // 3.0.0
            case 'models':      // 3.0.0
                $this->checkMandatoryFields(array('name'), $data);
                $fileName = $data['name'];
                break;

            case 'templates':
            case 'publicTemplates';
                $this->checkMandatoryFields(array('name', 'class'), $data);
                $fileName = "{$data['class']}-{$data['name']}";
                break;

            case 'scripts':
                $this->checkMandatoryFields(array('name', 'type'), $data);
                $fileName = "{$data['type']}-{$data['name']}";
                break;

            case 'styles':
                $this->checkMandatoryFields(array('name', 'type', 'template'), $data);
                $fileName = "{$data['type']}-{$data['name']}-{$data['template']}";
                break;

            case 'rules':
                if ($data['class'] === 'view') {
                    $this->checkMandatoryFields(array('viewName', 'class'), $data);
                    $fileName = "{$data['class']}-{$data['viewName']}";
                } elseif ($data['class'] === 'doc') {
                    $this->checkMandatoryFields(array('type', 'class'), $data);
                    $fileName = "{$data['class']}-{$data['type']}";
                } else {
                    $this->checkMandatoryFields(array('models', 'methods', 'class'), $data);
                    $models = implode(',', (array)$data['models']);
                    $methods = implode(',', (array)$data['methods']);
                    $fileName = "{$data['class']}-{$models}-{$methods}";
                }

                break;

            default:
                throw new \Exception("Неизвестный тип шаблона конфигурации {$type}");
        }

        return $fileName . '-' . $data['id'];
    }

    /**
     * @param array $fields
     * @param array $data
     * @throws \Exception
     */
    private function checkMandatoryFields($fields, $data) {
        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("Отсутствует обязательное поле \"{$field}\", id = {$data['id']}");
            }
        }
    }

}