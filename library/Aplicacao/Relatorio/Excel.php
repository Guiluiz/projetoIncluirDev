<?php

/**
 * Description of Excel
 *
 * @author Projeto Incluir
 */
require_once '../library/Aplicacao/Classes/PHPExcel.php';

class Aplicacao_Relatorio_Excel {

    private $letras;

    public function __construct() {
        $this->letras = range('A', 'Z');
    }

    /* public function getAlunos() {
      try {
      set_time_limit(0);
      @ini_set('memory_limit', '512M');
      $inputFileName = 'alunos_projeto_incluir.xlsx';
      $inputFileType = PHPExcel_IOFactory::identify($inputFileName);

      $objReader = PHPExcel_IOFactory::createReader('Excel2007');
      $objPHPExcel = $objReader->load($inputFileName);
      $objPHPExcel->setActiveSheetIndex(0);

      $worksheet = $objPHPExcel->getActiveSheet();
      $highestRow = $worksheet->getHighestRow(); // e.g. 10
      $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'

      $mapper_turmas = new Application_Model_Mappers_Turma();
      $turmas = $mapper_turmas->buscaTurmas();

      $mapper_aluno = new Application_Model_Mappers_Aluno();

      for ($linha = 1; $linha <= $highestRow; ++$linha) {
      $nome = trim((string) $worksheet->getCellByColumnAndRow(0, $linha)->getValue()) . ' ' . trim((string) $worksheet->getCellByColumnAndRow(1, $linha)->getValue());
      $disciplina = trim((string) $worksheet->getCellByColumnAndRow(2, $linha)->getValue());
      $email = trim((string) $worksheet->getCellByColumnAndRow(3, $linha)->getValue());
      $pagamentos = trim((string) $worksheet->getCellByColumnAndRow(4, $linha)->getValue());
      $cpf = $this->mask(trim((string) $worksheet->getCellByColumnAndRow(6, $linha)->getValue()), '###.###.###-##');
      $is_cpf_responsavel = ((strlen(trim((string) $worksheet->getCellByColumnAndRow(7, $linha)->getValue())) == 0) ? 0 : 1);
      $nome_responsavel = trim((string) $worksheet->getCellByColumnAndRow(7, $linha)->getValue());
      $escolaridade = trim((string) $worksheet->getCellByColumnAndRow(8, $linha)->getValue());
      $endereco = trim((string) $worksheet->getCellByColumnAndRow(9, $linha)->getValue());
      $numero = trim((string) $worksheet->getCellByColumnAndRow(10, $linha)->getValue());
      $complemento = trim((string) $worksheet->getCellByColumnAndRow(11, $linha)->getValue());
      $bairro = trim((string) $worksheet->getCellByColumnAndRow(12, $linha)->getValue());
      $cidade = trim((string) $worksheet->getCellByColumnAndRow(13, $linha)->getValue());
      $estado = "MG";
      $cep = $this->mask(trim((string) $worksheet->getCellByColumnAndRow(14, $linha)->getValue()), '##.###-###');
      $telefone_1 = $this->mask('0' . trim((string) $worksheet->getCellByColumnAndRow(15, $linha)->getValue()), '(###)####-####');
      $telefone_2 = $this->mask('0' . trim((string) $worksheet->getCellByColumnAndRow(16, $linha)->getValue()), '(###)####-####');
      $data_nascimento = trim((string) $worksheet->getCellByColumnAndRow(17, $linha)->getValue());
      $horarios = trim((string) $worksheet->getCellByColumnAndRow(18, $linha)->getValue());

      $array_turma_pagamentos = $this->buscaTurma($disciplina, $turmas, $pagamentos, $horarios);

      if (empty($array_turma_pagamentos))
      echo $linha . ' - ' . $nome . ' - ' . $this->retira_acentos($disciplina) . '-' . $horarios . '<br>';

      else {
      $aluno = new Application_Model_Aluno(null, $nome, $cpf, null, $data_nascimento, $email, $escolaridade, $telefone_1, $telefone_2, $endereco, $bairro, $numero, $complemento, $cep, $cidade, $estado, null, $is_cpf_responsavel, $nome_responsavel);
      foreach ($array_turma_pagamentos as $pagamentos) {
      $aluno->addTurma($pagamentos['turma'], '');
      $aluno->addPagamento($pagamentos['pagamento_turma']);
      }
      if (!$mapper_aluno->addAluno($aluno))
      var_dump($aluno);
      }
      }
      } catch (Exception $e) {
      die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
      }
      }

      public function buscaTurma($disciplina, $turmas, $pagamento, $horario) {
      $disciplinas = explode(';', $disciplina);
      $pagamentos = explode(';', $pagamento);
      $horarios = explode(';', $horario);
      // var_dump($horarios);
      $aux = array();

      //var_dump($this->retira_acentos($disciplina));
      foreach ($turmas as $turma) {
      //    var_dump($this->retira_acentos($turma->getDisciplina()->getNomeDisciplina()));

      if (!empty($disciplinas[0])) {
      $j = 0;
      for ($i = 0; $i < count($disciplinas); $i++) {
      $horario_inicio = DateTime::createFromFormat('H:i', $horarios[$i]);
      if ($this->retira_acentos($turma->getDisciplina()->getNomeDisciplina()) == $this->retira_acentos($disciplinas[$i]) && $turma->getHorarioInicio(true) == $horario_inicio) {
      $aux[$j]['turma'] = $turma;
      $aux[$j]['pagamento_turma'] = new Application_Model_Pagamento(null, $turma, 'Pendente', ((strtolower($pagamentos[$i]) == 'aprovado') ? 33 : 0));
      $j++;
      }
      }
      } else {
      $horario_inicio = DateTime::createFromFormat('H:i', $horario);

      if ($this->retira_acentos($turma->getDisciplina()->getNomeDisciplina()) == $this->retira_acentos($disciplina) && $turma->getHorarioInicio(true) == $horario_inicio)
      return array('turma' => $turma, 'pagamento_turma' => new Application_Model_Pagamento(null, $turma, 'Pendente', ((strtolower($pagamento) == 'aprovado') ? 33 : 0)));
      }
      }
      return $aux;
      }

      /* public function getPagamento($turmas, $disciplina, $status){

      }

      public function retira_acentos($texto) {
      $array1 = array(" - ", ",", "á", "à", "â", "ã", "ä", "é", "è", "ê", "ë", "í", "ì", "î", "ï", "ó", "ò", "ô", "õ", "ö", "ú", "ù", "û", "ü", "ç"
      , "Á", "À", "Â", "Ã", "Ä", "É", "È", "Ê", "Ë", "Í", "Ì", "Î", "Ï", "Ó", "Ò", "Ô", "Õ", "Ö", "Ú", "Ù", "Û", "Ü", "Ç");
      $array2 = array("_", "", "a", "a", "a", "a", "a", "e", "e", "e", "e", "i", "i", "i", "i", "o", "o", "o", "o", "o", "u", "u", "u", "u", "c"
      , "A", "A", "A", "A", "A", "E", "E", "E", "E", "I", "I", "I", "I", "O", "O", "O", "O", "O", "U", "U", "U", "U", "C");
      $aux = str_replace($array1, $array2, $texto);
      $aux = str_replace(' ', '_', $aux);
      return strtolower($aux);
      }

      public function mask($val, $mask) {
      $maskared = '';
      $k = 0;
      for ($i = 0; $i <= strlen($mask) - 1; $i++) {
      if ($mask[$i] == '#') {
      if (isset($val[$k]))
      $maskared .= $val[$k++];
      }
      else {
      if (isset($mask[$i]))
      $maskared .= $mask[$i];
      }
      }
      return $maskared;
      } */

    public function writeAlunos($alunos_turmas, $formato_saida) {
        try {
            //$aux_url = new Zend_View_Helper_BaseUrl();

            if (!empty($alunos_turmas)) {
                set_time_limit(0);
                @ini_set('memory_limit', '512M');

                $mapper_turma = new Application_Model_Mappers_Turma();
                $filter = new Aplicacao_Filtros_StringSimpleFilter();

                $excel = new PHPExcel();
                $excel->getProperties()->setCreator("Projeto Incluir")
                        ->setTitle("Relatório de Alunos por Turma");

                $excel->getDefaultStyle()->getFont()
                        ->setName('Calibri')
                        ->setSize(12)
                        ->setColor(new PHPExcel_Style_Color('#00000'));

                $image = file_get_contents('imagens/logo-projeto-incluir.PNG');
                file_put_contents('logo.png', $image);

                $indice_sheet = 0;
                $excel->removeSheetByIndex(0);
                $linhas = 0;

                foreach ($alunos_turmas as $id_turma => $alunos_turma) {
                    $turma = 'Sem Turma Definida';
                    if (!empty($id_turma))
                        $turma = $mapper_turma->buscaTurmaByID($id_turma);

                    $new_sheet = $excel->createSheet($indice_sheet);

                    $objDrawing = new PHPExcel_Worksheet_Drawing();
                    $objDrawing->setName('Logo Projeto Incluir')
                            ->setDescription('Logo Projeto Incluir')
                            ->setPath('logo.png')
                            ->setHeight(75)
                            ->setCoordinates('A1')
                            ->setOffsetX(20)
                            ->setWorksheet($new_sheet);

                    $new_sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    $new_sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                    $new_sheet->getPageSetup()->setFitToPage(true);
                    $new_sheet->getPageSetup()->setFitToWidth(1);
                    $new_sheet->getPageSetup()->setFitToHeight(0);

                    $new_sheet->setCellValue('A5', 'Nome do Aluno');
                    $new_sheet->setCellValue('B5', 'CPF');
                    $new_sheet->setCellValue('C5', 'Nome do Responsável');
                    $new_sheet->setCellValue('D5', 'Data de Nascimento');
                    $new_sheet->setCellValue('E5', 'RG');
                    $new_sheet->setCellValue('F5', 'Email');
                    $new_sheet->setCellValue('G5', 'Endereço');
                    $new_sheet->setCellValue('H5', 'Bairro');
                    $new_sheet->setCellValue('I5', 'Número');
                    $new_sheet->setCellValue('J5', 'Complemento');
                    $new_sheet->setCellValue('K5', 'CEP');
                    $new_sheet->setCellValue('L5', 'Cidade');
                    $new_sheet->setCellValue('M5', 'Estado');
                    $new_sheet->setCellValue('N5', 'Telefone 1');
                    $new_sheet->setCellValue('O5', 'Telefone 2');
                    $new_sheet->setCellValue('P5', 'Escolaridade');
                    $new_sheet->setCellValue('Q5', 'Pagamentos / Alimentos / Quantidades');
                    $new_sheet->setCellValue('R5', 'Pagamento Liberado');

                    $new_sheet->getColumnDimension('A')->setWidth(50);
                    $new_sheet->getColumnDimension('B')->setAutoSize(true);
                    $new_sheet->getColumnDimension('C')->setAutoSize(true);
                    $new_sheet->getColumnDimension('D')->setAutoSize(true);
                    $new_sheet->getColumnDimension('E')->setAutoSize(true);
                    $new_sheet->getColumnDimension('F')->setAutoSize(true);
                    $new_sheet->getColumnDimension('G')->setAutoSize(true);
                    $new_sheet->getColumnDimension('H')->setAutoSize(true);
                    $new_sheet->getColumnDimension('I')->setAutoSize(true);
                    $new_sheet->getColumnDimension('J')->setAutoSize(true);
                    $new_sheet->getColumnDimension('K')->setAutoSize(true);
                    $new_sheet->getColumnDimension('L')->setAutoSize(true);
                    $new_sheet->getColumnDimension('M')->setAutoSize(true);
                    $new_sheet->getColumnDimension('N')->setAutoSize(true);
                    $new_sheet->getColumnDimension('O')->setAutoSize(true);
                    $new_sheet->getColumnDimension('P')->setAutoSize(true);
                    $new_sheet->getColumnDimension('Q')->setAutoSize(true);
                    $new_sheet->getColumnDimension('R')->setAutoSize(true);

                    $i = 6;
                    foreach ($alunos_turma as $aluno) {
                        if ($aluno instanceof Application_Model_Aluno) {
                            $new_sheet->getRowDimension($i)->setRowHeight(20);

                            $new_sheet->setCellValue('A' . $i, mb_strtoupper($aluno->getNomeAluno(), 'UTF-8'));
                            $new_sheet->setCellValue('B' . $i, $aluno->getCpf());
                            $new_sheet->setCellValue('C' . $i, $aluno->getNomeResponsavel());
                            $new_sheet->setCellValue('D' . $i, $aluno->getDataNascimento(true));
                            $new_sheet->setCellValue('E' . $i, $aluno->getRg());
                            $new_sheet->setCellValue('F' . $i, $aluno->getEmail());
                            $new_sheet->setCellValue('G' . $i, $aluno->getEndereco());
                            $new_sheet->setCellValue('H' . $i, $aluno->getBairro());
                            $new_sheet->setCellValue('I' . $i, $aluno->getNumeroEndereco());
                            $new_sheet->setCellValue('J' . $i, $aluno->getComplemento());
                            $new_sheet->setCellValue('K' . $i, $aluno->getCep());
                            $new_sheet->setCellValue('L' . $i, $aluno->getCidade());
                            $new_sheet->setCellValue('M' . $i, $aluno->getEstado());
                            $new_sheet->setCellValue('N' . $i, $aluno->getTelefoneFixo());
                            $new_sheet->setCellValue('O' . $i, $aluno->getTelefoneCelular());
                            $new_sheet->setCellValue('P' . $i, $aluno->getEscolaridade());

                            $aux_pagamento = '';
                            $aux_situacao = '';

                            $pagamento = $aluno->getPagamentoTurma($id_turma);

                            if ($pagamento instanceof Application_Model_Pagamento) {
                                $aux_pagamento .= "Pagamento: ";

                                $aux_situacao .= $pagamento->getSituacao();
                                $aux_pagamento .= "R$" . $pagamento->getValorPagamento(true) . "\n";
                                $aux_pagamento .= "Condição: " . $pagamento->getCondicaoMatriculaToString() . "\n";
                                $aux_pagamento .= "Recibo: " . $pagamento->getReciboToString() . "\n";

                                if ($pagamento->hasAlimentos()) {
                                    $aux_pagamento .= "Alimento(s)/Quantidade: \n";

                                    foreach ($pagamento->getAlimentos() as $alimento)
                                        $aux_pagamento .= $alimento[Application_Model_Pagamento::$index_alimento]->getNomeAlimento() . ' / ' . $alimento[Application_Model_Pagamento::$index_quantidade_alimento] . "\n";
                                }
                            }

                            $new_sheet->setCellValue('Q' . $i, $aux_pagamento);
                            $new_sheet->setCellValue('R' . $i, $aux_situacao);

                            $new_sheet->getStyle('A' . $i . ':R' . $i)->applyFromArray(
                                    array('alignment' => array(
                                            'wrap' => true,
                                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                        ),
                                        'borders' => array(
                                            'allborders' => array(
                                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                'color' => array('argb' => '000000'),
                                            ),
                                        ),)
                            );

                            if ($i % 2 != 0) {
                                $new_sheet->getStyle('A' . $i . ':R' . $i)->applyFromArray(
                                        array(
                                            'fill' => array(
                                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                                'startcolor' => array('rgb' => 'F1F1F1'),
                                            ))
                                );
                            }

                            $i++;
                        }
                    }
                    $linhas += $i;
                    //$new_sheet->getStyle('A0:P' . $linhas)->getAlignment()->setWrapText(true);
                    $new_sheet->getStyle('A5:R5')->applyFromArray(
                            array('borders' => array(
                                    'allborders' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                                        'color' => array('argb' => '000000'),
                                    ),
                                ),
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'startcolor' => array('rgb' => 'CCCCCC'),
                                ),
                                'alignment' => array(
                                    'wrap' => true,
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                ))
                    );
                    $new_sheet->getRowDimension('5')->setRowHeight(25);

                    if ($turma instanceof Application_Model_Turma) {
                        $new_sheet->setTitle($filter->filter($turma->getNomeTurma()));
                        $new_sheet->mergeCells('C3:F3');
                        $new_sheet->mergeCells('C4:F4');

                        $new_sheet->setCellValue('C3', "Turma: " . $turma->toString());
                        $new_sheet->setCellValue('C4', "Horário: " . $turma->horarioTurmaToString());
                    } else
                        $new_sheet->setTitle($turma);

                    $indice_sheet++;
                }

                $data = new DateTime();

                if ($formato_saida == 'xls') {
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="relatorio_alunos_turma_' . $data->format('d_m_Y') . '.xls"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
                    $objWriter->save('php://output');
                } else {
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="relatorio_alunos_turma_' . $data->format('d_m_Y') . '.xlsx"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                    $objWriter->save('php://output');
                }

                unlink('logo.png');
                return true;
            }
            return null;
        } catch (Exception $e) {
            echo $e;
            return false;
        }
    }

    public function writeAlunosUnicoSheet($alunos, $formato_saida) {
        try {
            if (!empty($alunos)) {
                set_time_limit(0);
                @ini_set('memory_limit', '512M');

                $mapper_turma = new Application_Model_Mappers_Turma();
                //$filter = new Aplicacao_Filtros_StringSimpleFilter();

                $excel = new PHPExcel();
                $excel->getProperties()->setCreator("Projeto Incluir")
                        ->setTitle("Relatório de Alunos por Turma");

                $excel->getDefaultStyle()->getFont()
                        ->setName('Calibri')
                        ->setSize(12)
                        ->setColor(new PHPExcel_Style_Color('#00000'));

                $image = file_get_contents('imagens/logo-projeto-incluir.PNG');
                file_put_contents('logo.png', $image);

                $sheet = $excel->getActiveSheet();
                $linhas = 0;

                $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                $sheet->getStyle('B2')->applyFromArray(
                        array('alignment' => array(
                                'wrap' => true,
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                            ),
                            'font' => array(
                                'bold' => true,
                                'underline' => true,
                                'size' => 12
                            ))
                );

                $sheet->setCellValue('B2', 'Relatório Geral');

                $sheet->setCellValue('A5', 'Nome do Aluno');
                $sheet->setCellValue('B5', 'Disciplina - Turma | Horário');
                $sheet->setCellValue('C5', 'CPF');
                $sheet->setCellValue('D5', 'Nome do Responsável');
                $sheet->setCellValue('E5', 'Data de Nascimento');
                $sheet->setCellValue('F5', 'RG');
                $sheet->setCellValue('G5', 'Email');
                $sheet->setCellValue('H5', 'Endereço');
                $sheet->setCellValue('I5', 'Bairro');
                $sheet->setCellValue('J5', 'Número');
                $sheet->setCellValue('K5', 'Complemento');
                $sheet->setCellValue('L5', 'CEP');
                $sheet->setCellValue('M5', 'Cidade');
                $sheet->setCellValue('N5', 'Estado');
                $sheet->setCellValue('O5', 'Telefone 1');
                $sheet->setCellValue('P5', 'Telefone 2');
                $sheet->setCellValue('Q5', 'Escolaridade');
                $sheet->setCellValue('R5', 'Pagamentos / Alimentos / Quantidades');
                $sheet->setCellValue('S5', 'Pagamento Liberado');

                $sheet->getColumnDimension('A')->setWidth(50);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);
                $sheet->getColumnDimension('F')->setAutoSize(true);
                $sheet->getColumnDimension('G')->setAutoSize(true);
                $sheet->getColumnDimension('H')->setAutoSize(true);
                $sheet->getColumnDimension('I')->setAutoSize(true);
                $sheet->getColumnDimension('J')->setAutoSize(true);
                $sheet->getColumnDimension('K')->setAutoSize(true);
                $sheet->getColumnDimension('L')->setAutoSize(true);
                $sheet->getColumnDimension('M')->setAutoSize(true);
                $sheet->getColumnDimension('N')->setAutoSize(true);
                $sheet->getColumnDimension('O')->setAutoSize(true);
                $sheet->getColumnDimension('P')->setAutoSize(true);
                $sheet->getColumnDimension('Q')->setAutoSize(true);
                $sheet->getColumnDimension('R')->setAutoSize(true);
                $sheet->getColumnDimension('S')->setAutoSize(true);

                $objDrawing = new PHPExcel_Worksheet_Drawing();
                $objDrawing->setName('Logo Projeto Incluir')
                        ->setDescription('Logo Projeto Incluir')
                        ->setPath('logo.png')
                        ->setHeight(75)
                        ->setCoordinates('A1')
                        ->setOffsetX(20)
                        ->setWorksheet($sheet);

                $i = 6;
                foreach ($alunos as $aluno) {
                    if ($aluno instanceof Application_Model_Aluno) {
                        $sheet->getRowDimension($i)->setRowHeight(20);

                        $sheet->setCellValue('A' . $i, mb_strtoupper($aluno->getNomeAluno(), 'UTF-8'));

                        $sheet->setCellValue('C' . $i, $aluno->getCpf());
                        $sheet->setCellValue('D' . $i, $aluno->getNomeResponsavel());
                        $sheet->setCellValue('E' . $i, $aluno->getDataNascimento(true));
                        $sheet->setCellValue('F' . $i, $aluno->getRg());
                        $sheet->setCellValue('G' . $i, $aluno->getEmail());
                        $sheet->setCellValue('H' . $i, $aluno->getEndereco());
                        $sheet->setCellValue('I' . $i, $aluno->getBairro());
                        $sheet->setCellValue('J' . $i, $aluno->getNumeroEndereco());
                        $sheet->setCellValue('K' . $i, $aluno->getComplemento());
                        $sheet->setCellValue('L' . $i, $aluno->getCep());
                        $sheet->setCellValue('M' . $i, $aluno->getCidade());
                        $sheet->setCellValue('N' . $i, $aluno->getEstado());
                        $sheet->setCellValue('O' . $i, $aluno->getTelefoneFixo());
                        $sheet->setCellValue('P' . $i, $aluno->getTelefoneCelular());
                        $sheet->setCellValue('Q' . $i, $aluno->getEscolaridade());

                        if ($aluno->hasTurmas()) {
                            $aux_turma = '';
                            foreach ($aluno->getCompleteTurmas() as $id_turma => $turma) {
                                $aux_nome_turma = 'Sem Turma Definida';
                                if (!empty($id_turma)) {
                                    $aux_turma = $mapper_turma->buscaTurmaByID($id_turma);

                                    if ($aux_turma instanceof Application_Model_Turma) {
                                        $aux_nome_turma = $aux_turma->toString() . ' | ' . $aux_turma->horarioTurmaToString();

                                        $aux_pagamento = '';
                                        $aux_situacao = '';

                                        $pagamento = $turma[Application_Model_Aluno::$index_pagamento_turma];

                                        if ($pagamento instanceof Application_Model_Pagamento) {
                                            $aux_pagamento .= "Pagamento: ";

                                            $aux_situacao .= $pagamento->getSituacao();
                                            $aux_pagamento .= "R$" . $pagamento->getValorPagamento(true) . "\n";
                                            $aux_pagamento .= "Condição: " . $pagamento->getCondicaoMatriculaToString() . "\n";
                                            $aux_pagamento .= "Recibo: " . $pagamento->getReciboToString() . "\n";

                                            if ($pagamento->hasAlimentos()) {
                                                $aux_pagamento .= "Alimento(s)/Quantidade: \n";

                                                foreach ($pagamento->getAlimentos() as $alimento)
                                                    $aux_pagamento .= $alimento[Application_Model_Pagamento::$index_alimento]->getNomeAlimento() . ' / ' . $alimento[Application_Model_Pagamento::$index_quantidade_alimento] . "\n";
                                            }
                                        }

                                        $sheet->setCellValue('R' . $i, $aux_pagamento);
                                        $sheet->setCellValue('S' . $i, $aux_situacao);
                                        $sheet->setCellValue('B' . $i, $aux_nome_turma);
                                    }
                                }
                            }
                        }

                        $sheet->getStyle('A' . $i . ':S' . $i)->applyFromArray(
                                array('alignment' => array(
                                        'wrap' => true,
                                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                    ),
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => '000000'),
                                        ),
                                    ),)
                        );

                        if ($i % 2 != 0) {
                            $sheet->getStyle('A' . $i . ':S' . $i)->applyFromArray(
                                    array(
                                        'fill' => array(
                                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                            'startcolor' => array('rgb' => 'F1F1F1'),
                                        ))
                            );
                        }

                        $i++;
                    }
                }

                $linhas += $i;
                //$new_sheet->getStyle('A0:P' . $linhas)->getAlignment()->setWrapText(true);
                $sheet->getStyle('A5:S5')->applyFromArray(
                        array('borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => array('argb' => '000000'),
                                ),
                            ),
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'startcolor' => array('rgb' => 'CCCCCC'),
                            ),
                            'alignment' => array(
                                'wrap' => true,
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                            ))
                );
                $sheet->getRowDimension('5')->setRowHeight(25);
                $sheet->setTitle('Relatório Geral');

                $data = new DateTime();

                if ($formato_saida == 'xls') {
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="relatorio_alunos_turma_' . $data->format('d_m_Y') . '.xls"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
                    $objWriter->save('php://output');
                } else {
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="relatorio_alunos_turma_' . $data->format('d_m_Y') . '.xlsx"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                    $objWriter->save('php://output');
                }

                unlink('logo.png');
                return true;
            }
            return null;
        } catch (Exception $e) {
            echo $e;
            return false;
        }
    }

    public function getListaPresenca($alunos_turmas, $formato_saida, $data) {
        try {
            if (!empty($alunos_turmas)) {
                set_time_limit(0);
                @ini_set('memory_limit', '512M');

                $image = file_get_contents('imagens/logo-projeto-incluir.PNG');
                file_put_contents('logo.png', $image);

                $mapper_turma = new Application_Model_Mappers_Turma();
                $filter = new Aplicacao_Filtros_StringSimpleFilter();

                if (!empty($data)) {
                    $data = DateTime::createFromFormat('d/m/Y', $data);

                    if (!$data instanceof DateTime)
                        $data = new DateTime();
                } else
                    $data = new DateTime();

                $excel = new PHPExcel();
                $excel->getProperties()->setCreator("Projeto Incluir")
                        ->setTitle("Lista de Presença");

                $excel->getDefaultStyle()->getFont()
                        ->setName('Calibri')
                        ->setSize(12)
                        ->setColor(new PHPExcel_Style_Color('#00000'));

                $indice_sheet = 0;
                $excel->removeSheetByIndex(0);
                $linhas = 0;

                foreach ($alunos_turmas as $id_turma => $alunos_turma) {
                    $turma = 'Sem Turma Definida';
                    if (!empty($id_turma))
                        $turma = $mapper_turma->buscaTurmaByID($id_turma);

                    $new_sheet = $excel->createSheet($indice_sheet);

                    $objDrawing = new PHPExcel_Worksheet_Drawing();
                    $objDrawing->setName('Logo Projeto Incluir')
                            ->setDescription('Logo Projeto Incluir')
                            ->setPath('logo.png')
                            ->setHeight(75)
                            ->setCoordinates('A1')
                            ->setOffsetX(40)
                            ->setWorksheet($new_sheet);

                    $new_sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    $new_sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                    $new_sheet->getPageSetup()->setFitToPage(true);
                    $new_sheet->getPageSetup()->setFitToWidth(1);
                    $new_sheet->getPageSetup()->setFitToHeight(0);


                    $new_sheet->setCellValue('B1', "LISTA DE PRESENÇA");
                    $new_sheet->getStyle('B1:B2')->applyFromArray(
                            array(
                                'alignment' => array(
                                    'wrap' => true,
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                ),
                                'font' => array(
                                    'bold' => true,
                                    'underline' => true,
                                    'size' => 18
                                ))
                    );

                    $new_sheet->getRowDimension(1)->setRowHeight(30);

                    $new_sheet->setCellValue('B2', 'Data: ' . $data->format('d/m/Y'));

                    $new_sheet->getStyle('B2')->getFont()
                            ->setSize(10);

                    $new_sheet->mergeCells('A4:C4');
                    $new_sheet->setCellValue('A4', 'Nome legível do professor (1): ___________________________________________________________   Nome legível do professor (2): _______________________________________________________________________');
                    $new_sheet->getStyle('A4')->getFont()
                            ->setSize(10);

                    $new_sheet->getRowDimension(4)->setRowHeight(25);

                    $new_sheet->getStyle('A4:C4')->applyFromArray(
                            array('borders' => array(
                                    'allborders' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                                        'color' => array('argb' => '000000'),
                                    ),
                                ),
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'startcolor' => array('rgb' => 'CCCCCC'),
                                ),
                                'alignment' => array(
                                    'wrap' => true,
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                ))
                    );

                    $new_sheet->setCellValue('A5', 'Aluno(a)');
                    $new_sheet->setCellValue('B5', 'E-mail');
                    $new_sheet->setCellValue('C5', 'Assinatura (Nome completo e legível)');


                    $new_sheet->getColumnDimension('A')->setWidth(50);
                    $new_sheet->getColumnDimension('B')->setWidth(45);
                    $new_sheet->getColumnDimension('C')->setWidth(70);

                    $i = 6;
                    foreach ($alunos_turma as $aluno) {
                        $new_sheet->setCellValue('A' . $i, mb_strtoupper($aluno->getNomeAluno(), 'UTF-8'));
                        $new_sheet->setCellValue('B' . $i, $aluno->getEmail());
                        $new_sheet->getRowDimension($i)->setRowHeight(25);

                        $new_sheet->getStyle('A' . $i . ':C' . $i)->applyFromArray(
                                array('borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => '000000'),
                                        ),
                                    ),
                                    'alignment' => array(
                                        'wrap' => true,
                                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                    ))
                        );

                        if ($i % 2 != 0) {
                            $new_sheet->getStyle('A' . $i . ':C' . $i)->applyFromArray(
                                    array('fill' => array(
                                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                            'startcolor' => array('rgb' => 'CCCCCC'),
                                        ))
                            );
                        }

                        $i++;
                    }
                    $linhas += $i;
                    //$new_sheet->getStyle('A0:P' . $linhas)->getAlignment()->setWrapText(true);
                    $new_sheet->getStyle('A5:C5')->applyFromArray(
                            array('borders' => array(
                                    'allborders' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                                        'color' => array('argb' => '000000'),
                                    ),
                                ),
                                'alignment' => array(
                                    'wrap' => true,
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                ),
                                'font' => array(
                                    'bold' => true,
                                    'underline' => true,
                                    'size' => 12
                                ))
                    );
                    $new_sheet->getRowDimension('5')->setRowHeight(30);

                    if ($turma instanceof Application_Model_Turma) {
                        $new_sheet->setTitle($filter->filter($turma->getNomeTurma()));

                        $new_sheet->setCellValue('C1', "Turma: " . $turma->toString());
                        $new_sheet->setCellValue('C2', "Horário: " . $turma->horarioTurmaToString());
                        $new_sheet->setCellValue('C3', "Professor(es): " . $turma->getNomesProfessores() . ' | ' . "Sala:" . $turma->getSala());

                        $new_sheet->getStyle('C1:C3')->getFont()
                                ->setSize(10);
                    } else
                        $new_sheet->setTitle($turma);

                    $indice_sheet++;
                }

                if ($formato_saida == 'xls') {
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="lista_presenca_' . $data->format('d_m_Y') . '.xls"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
                    $objWriter->save('php://output');
                } else {
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="lista_presenca_' . $data->format('d_m_Y') . '.xlsx"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                    $objWriter->save('php://output');
                }

                unlink('logo.png');
                return true;
            }
            return null;
        } catch (Exception $e) {
            echo $e;
            return true;
        }
    }

    public function getRelatorioFrequenciaAluno($alunos_turmas, $formato_saida, $calendario) {
        try {
            setlocale(LC_ALL, "pt_BR", "pt_BR.iso-8859-1", "pt_BR.utf-8", "portuguese");
            date_default_timezone_set('America/Sao_Paulo');
            @ini_set('memory_limit', '768M');

            if (!empty($alunos_turmas) && $calendario instanceof Application_Model_DatasAtividade) {
                set_time_limit(0);

                $image = file_get_contents('imagens/logo-projeto-incluir.PNG');
                file_put_contents('logo.png', $image);

                $mapper_turma = new Application_Model_Mappers_Turma();
                $mapper_frequencia = new Application_Model_Mappers_Frequencia();

                $filter = new Aplicacao_Filtros_StringSimpleFilter();

                $data = new DateTime();
                $timestamp_atual = $data->getTimestamp();
                $array_datas_cell = array();

                $excel = new PHPExcel();
                $excel->getProperties()->setCreator("Projeto Incluir")
                        ->setTitle("Frequência de Alunos");

                $excel->getDefaultStyle()->getFont()
                        ->setName('Calibri')
                        ->setSize(12)
                        ->setColor(new PHPExcel_Style_Color('#00000'));

                $indice_sheet = 0;
                $excel->removeSheetByIndex(0);
                $linhas = 0;

                $datas = $calendario->getDatas();

                $j = 1;

                if (!empty($datas)) {
                    foreach ($datas as $data_letiva) {
                        $letra = $this->getLetra($j);
                        $timestamp = $data_letiva->getTimestamp();

                        $array_datas_cell[$timestamp] = $letra;
                        $j++;
                    }
                }

                //unset($mapper_datas_calendario_letivo);
                //unset($datas);

                foreach ($alunos_turmas as $id_turma => $alunos_turma) {
                    $turma = 'Sem Turma Definida';
                    $datas_lancamento_turma = array();

                    if (!empty($id_turma)) {
                        $turma = $mapper_turma->buscaTurmaByID($id_turma);
                        $datas_lancamento_turma = $mapper_frequencia->getDatasLancamentosByPeriodo(null, $id_turma);
                    }

                    $new_sheet = $excel->createSheet($indice_sheet);

                    $objDrawing = new PHPExcel_Worksheet_Drawing();
                    $objDrawing->setName('Logo Projeto Incluir')
                            ->setDescription('Logo Projeto Incluir')
                            ->setPath('logo.png')
                            ->setHeight(75)
                            ->setCoordinates('A1')
                            ->setOffsetX(40)
                            ->setWorksheet($new_sheet);

                    $new_sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    $new_sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                    $new_sheet->getPageSetup()->setFitToPage(true);
                    $new_sheet->getPageSetup()->setFitToWidth(1);
                    $new_sheet->getPageSetup()->setFitToHeight(0);


                    $new_sheet->setCellValue('B1', "Frequência de Alunos");
                    $new_sheet->getStyle('B1:B2')->applyFromArray(
                            array(
                                'alignment' => array(
                                    'wrap' => true,
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                ),
                                'font' => array(
                                    'bold' => true,
                                    'underline' => true,
                                    'size' => 18
                                ))
                    );

                    $new_sheet->getRowDimension(1)->setRowHeight(30);
                    $new_sheet->getRowDimension(4)->setRowHeight(50);

                    if ($turma instanceof Application_Model_Turma) {
                        $new_sheet->setTitle($filter->filter($turma->getNomeTurma()));

                        $new_sheet->setCellValue('B4', "Turma: " . $turma->toString() . "\nHorário: " . $turma->horarioTurmaToString());
                        $new_sheet->getStyle('C4:E4')->getFont()
                                ->setSize(11);
                    } else
                        $new_sheet->setTitle($turma);

                    if (!empty($array_datas_cell)) {
                        foreach ($array_datas_cell as $timestamp => $letra)
                            $new_sheet->setCellValue($letra . '5', strftime("%d/%b", $timestamp)); //$data_letiva->format('d/M'));
                    }

                    $new_sheet->mergeCells('B1:' . $this->getLetra($j - 1) . '1'); // linha título
                    $new_sheet->mergeCells('B4:' . $this->getLetra($j - 1) . '4'); // linha turma

                    $new_sheet->getStyle('A5:' . $this->getLetra($j - 1) . '5')->applyFromArray(
                            array('borders' => array(
                                    'allborders' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                                        'color' => array('argb' => '000000'),
                                    ),
                                ),
                                'alignment' => array(
                                    'wrap' => true,
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                ),
                                'font' => array(
                                    'bold' => true,
                                    'underline' => true,
                                    'size' => 12
                                ))
                    );


                    $new_sheet->setCellValue('A5', 'Aluno(a)');
                    $new_sheet->getColumnDimension('A')->setWidth(50);

                    $i = 6;
                    foreach ($alunos_turma as $aluno) {
                        $faltas = $aluno->getFaltas();
                        $new_sheet->setCellValue('A' . $i, mb_strtoupper($aluno->getNomeAluno(), 'UTF-8'));
                        if (!empty($faltas) && isset($faltas[base64_encode($id_turma)])) {
                            $faltas = $faltas[base64_encode($id_turma)];

                            foreach ($array_datas_cell as $timestamp => $cell) {
                                if ($timestamp < $timestamp_atual && isset($datas_lancamento_turma[$id_turma][date('Y-m-d', $timestamp)])) {
                                    if (isset($faltas[$timestamp]))
                                        $new_sheet->setCellValue($cell . $i, 'A');
                                    else
                                        $new_sheet->setCellValue($cell . $i, 'P');
                                } else
                                    $new_sheet->setCellValue($cell . $i, '-');
                            }
                        }

                        $new_sheet->getRowDimension($i)->setRowHeight(25);

                        /* $new_sheet->getStyle('A' . $i . ':' . $this->getLetra($j - 1) . $i)->applyFromArray(
                          array('borders' => array(
                          'allborders' => array(
                          'style' => PHPExcel_Style_Border::BORDER_THIN,
                          'color' => array('argb' => '000000'),
                          ),
                          ),
                          'alignment' => array(
                          'wrap' => true,
                          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                          ))
                          );

                          if ($i % 2 != 0) {
                          $new_sheet->getStyle('A' . $i . ':' . $this->getLetra($j - 1) . $i)->applyFromArray(
                          array('fill' => array(
                          'type' => PHPExcel_Style_Fill::FILL_SOLID,
                          'startcolor' => array('rgb' => 'CCCCCC'),
                          ))
                          );
                          } */

                        $i++;
                    }
                    $linhas += $i;
                    $new_sheet->getStyle('A0:P' . $linhas)->getAlignment()->setWrapText(true);

                    $new_sheet->getStyle('A6:' . $this->getLetra($j - 1) . ($i - 1))->applyFromArray(
                            array('borders' => array(
                                    'allborders' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                                        'color' => array('argb' => '000000'),
                                    ),
                                ),
                                'alignment' => array(
                                    'wrap' => true,
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                ))
                    );
                    $indice_sheet++;
                }

                if ($formato_saida == 'xls') {
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="lista_presenca_' . $data->format('d_m_Y') . '.xls"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
                    $objWriter->save('php://output');
                } else {
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="relatório_presenca_alunos_' . $data->format('d_m_Y') . '.xlsx"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                    $objWriter->save('php://output');
                }

                unlink('logo.png');
                return true;
            }
            return null;
        } catch (Exception $e) {
            echo $e;
            return true;
        }
    }

    public function getRelatorioNotasAluno($alunos, $formato_saida) {
        try {
            if (!empty($alunos)) {
                set_time_limit(0);
                @ini_set('memory_limit', '512M');

                $array_aprovacao = array(
                    Application_Model_Aluno::$aluno_aprovado => 'Aprovado',
                    Application_Model_Aluno::$aluno_reprovado => 'Reprovado',
                    Application_Model_Aluno::$aluno_sem_status_aprovacao => '-');

                $mapper_turma = new Application_Model_Mappers_Turma();
                $mapper_frequencia = new Application_Model_Mappers_Frequencia();

                $excel = new PHPExcel();
                $excel->getProperties()->setCreator("Projeto Incluir")
                        ->setTitle("Notas dos Alunos");

                $excel->getDefaultStyle()->getFont()
                        ->setName('Calibri')
                        ->setSize(12)
                        ->setColor(new PHPExcel_Style_Color('#00000'));

                $image = file_get_contents('imagens/logo-projeto-incluir.PNG');
                file_put_contents('logo.png', $image);

                $sheet = $excel->getActiveSheet();
                $linhas = 0;

                $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                $sheet->getStyle('B2')->applyFromArray(
                        array('alignment' => array(
                                'wrap' => true,
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                            ),
                            'font' => array(
                                'bold' => true,
                                'underline' => true,
                                'size' => 12
                            ))
                );

                $sheet->setCellValue('B2', 'Notas/Frequências dos Alunos');

                $sheet->setCellValue('A5', 'Nome do Aluno');
                $sheet->setCellValue('B5', 'Turma');
                $sheet->setCellValue('C5', 'Email');
                $sheet->setCellValue('D5', 'Nota Acumulada/Total Distribuído');
                $sheet->setCellValue('E5', 'Frequência (%)');
                $sheet->setCellValue('F5', 'Situação Final');

                $sheet->getColumnDimension('A')->setWidth(50);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);

                $objDrawing = new PHPExcel_Worksheet_Drawing();
                $objDrawing->setName('Logo Projeto Incluir')
                        ->setDescription('Logo Projeto Incluir')
                        ->setPath('logo.png')
                        ->setHeight(75)
                        ->setCoordinates('A1')
                        ->setOffsetX(20)
                        ->setWorksheet($sheet);

                $i = 6;
                foreach ($alunos as $aluno) {
                    if ($aluno instanceof Application_Model_Aluno) {
                        $sheet->getRowDimension($i)->setRowHeight(20);

                        $sheet->setCellValue('A' . $i, mb_strtoupper($aluno->getNomeAluno(), 'UTF-8'));
                        $sheet->setCellValue('C' . $i, $aluno->getEmail());

                        if ($aluno->hasTurmas()) {
                            $aux_turma = '';

                            foreach ($aluno->getCompleteTurmas() as $id_turma => $turma) {
                                $total_aulas = $mapper_frequencia->getQuantidadeLancamentosByPeriodo(array($id_turma));

                                $aux_nome_turma = 'Sem Turma Definida';

                                if (isset($total_aulas[$id_turma]))
                                    $total_aulas = $total_aulas[$id_turma];
                                else
                                    $total_aulas = 0;

                                if (!empty($id_turma)) {
                                    $aux_turma = $mapper_turma->buscaTurmaByID($id_turma);

                                    if ($aux_turma instanceof Application_Model_Turma)
                                        $aux_nome_turma = $aux_turma->toString() . ' | ' . $aux_turma->horarioTurmaToString();
                                }

                                $sheet->setCellValue('B' . $i, $aux_nome_turma);
                                $sheet->setCellValue('D' . $i, $aluno->getNotaAcumulada($id_turma, false));
                                $sheet->setCellValue('E' . $i, $aluno->getPorcentagemFaltas($id_turma, $total_aulas, true));
                                $sheet->setCellValue('F' . $i, $array_aprovacao[$turma[Application_Model_Aluno::$index_aprovacao_turma]]);
                            }
                        }

                        $sheet->getStyle('A' . $i . ':F' . $i)->applyFromArray(
                                array('alignment' => array(
                                        'wrap' => true,
                                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                    ),
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => '000000'),
                                        ),
                                    ),)
                        );

                        if ($i % 2 != 0) {
                            $sheet->getStyle('A' . $i . ':F' . $i)->applyFromArray(
                                    array(
                                        'fill' => array(
                                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                            'startcolor' => array('rgb' => 'F1F1F1'),
                                        ))
                            );
                        }

                        $i++;
                    }
                }


                $linhas += $i;
                //$new_sheet->getStyle('A0:P' . $linhas)->getAlignment()->setWrapText(true);
                $sheet->getStyle('A5:F5')->applyFromArray(
                        array('borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => array('argb' => '000000'),
                                ),
                            ),
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'startcolor' => array('rgb' => 'CCCCCC'),
                            ),
                            'alignment' => array(
                                'wrap' => true,
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                            ))
                );
                $sheet->getRowDimension('5')->setRowHeight(25);
                $sheet->setTitle('Notas de Alunos');

                $data = new DateTime();

                if ($formato_saida == 'xls') {
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="notas_frequencia_alunos_' . $data->format('d_m_Y') . '.xls"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
                    $objWriter->save('php://output');
                } else {
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="notas_frequencia_alunos_' . $data->format('d_m_Y') . '.xlsx"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                    $objWriter->save('php://output');
                }

                unlink('logo.png');
                return true;
            }
            return null;
        } catch (Exception $e) {
            echo $e;
            return false;
        }
    }

    private function getLetra($pos) {
        $contador = count($this->letras);
        if (isset($this->letras[$pos]))
            return $this->letras[$pos];
        elseif ($pos > ($contador - 1))
            return $this->letras[(int) ($pos / $contador)] . $this->letras[(int) ($pos % $contador)];
        else
            return null;
    }

}

?>
