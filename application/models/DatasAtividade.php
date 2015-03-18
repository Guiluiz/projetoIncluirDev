<?php
/**
 * Classe para gerenciar o calendário acadêmico dos períodos cadastrados
 * @author Projeto Incluir
 */
class Application_Model_DatasAtividade {
    
    /**
     *
     * @var DateTime[]
     */
    private $datas;
    
    /**
     *
     * @var Application_Model_Periodo 
     */
    private $periodo;
    
    public function __construct($periodo) {
        $this->datas = array();
        $this->periodo = $periodo;
    }

    /**
     * Inclui uma nova data no array de datas.
     * @param DateTime $data
     */
    public function addData($data) {
        $data = $this->parseDate($data);
        if ($data instanceof DateTime && !isset($this->datas[$data->format('d/m/Y')]))
            $this->datas[$data->format('d/m/Y')] = $data;
    }

    /**
     * Retorna um array contendo as datas, de acordo com o formato indicado
     * @param boolean $isView
     * @return string[]
     */
    public function parseArray($isView = null) {
        $aux = array();
        $format = (!empty($isView)) ? 'd/m/Y' : 'Y-m-d';

        foreach ($this->datas as $data) {
            $date_format = $data->format($format);
            $aux[$date_format] = $date_format;
        }
        return $aux;
    }

    /**
     * Retorna o array de datas
     * @return DateTime[]
     */
    public function getDatas() {
        return $this->datas;
    }
    
    /**
     * Retorna a quantidade de aulas.
     * @param boolena $ate_atual Indica se serão contadas as datas até o momento atual
     * @return int
     */
    public function getQuantidadeAulas($ate_atual = true) {
        if ($ate_atual) {
            $count = 0;
            $data_atual = new DateTime();

            foreach ($this->datas as $data) {
                if ($data_atual > $data)
                    $count++;
            }
            return $count;
        }

        return count($this->datas);
    }

    /**
     * Converte uma string em dateTime
     * @param DateTime|string $data
     * @return null|\DateTime
     */
    public function parseDate($data) {
        if (!$data instanceof DateTime) {
            if (!empty($data)) {
                if (strpos($data, '-') === false)
                    return DateTime::createFromFormat('d/m/Y', $data);
                return new DateTime($data);
            }
            return null;
        }
        return $data;
    }
    
    /**
     * Retorna o período ao qual o calendário pertence
     * @return Application_Model_Periodo
     */
    public function getPeriodoCalendario(){
        return $this->periodo;
    }

}
