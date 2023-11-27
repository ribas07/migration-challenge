<?php
/*
  Descrição do Desafio:
    Você precisa realizar uma migração dos dados fictícios que estão na pasta <dados_sistema_legado> para a base da clínica fictícia MedicalChallenge.
    Para isso, você precisa:
      1. Instalar o MariaDB na sua máquina. Dica: Você pode utilizar Docker para isso;
      2. Restaurar o banco da clínica fictícia Medical Challenge: arquivo <medical_challenge_schema>;
      3. Migrar os dados do sistema legado fictício que estão na pasta <dados_sistema_legado>:
        a) Dica: você pode criar uma função para importar os arquivos do formato CSV para uma tabela em um banco temporário no seu MariaDB.
      4. Gerar um dump dos dados já migrados para o banco da clínica fictícia Medical Challenge.
*/

// Importação de Bibliotecas:
include "./lib.php";

// Conexão com o banco da clínica fictícia:
$connMedical = mysqli_connect("localhost", "root", "root", "MedicalChallenge")
  or die("Não foi possível conectar os servidor MySQL: MedicalChallenge\n");

// Conexão com o banco temporário:
$connTemp = mysqli_connect("localhost", "root", "root", "MedicalChallengeTemp")
  or die("Não foi possível conectar os servidor MySQL: MedicalChallengeTemp\n");

// Informações de Inicio da Migração:
echo "Início da Migração: " . dateNow() . ".\n\n";


                                                    /*------- INÍCIO CÓDIGO DESAFIO - ISRAEL RIBAS JACINTO -------*/
/*
Aqui estou alterando a estrutura da tabela "pacientes" para incluir três novas colunas (nome do pai do paciente, nome da mãe do paciente e observações da clinica)
e também inserir um valor de codigo de convenio na tabela "convenios" que o sistema novo não possuía,
assim eu consigo inserir os dados na tabela "pacientes" que possuem o id_convenio valor 5 como chave estrangeira.

Por que alterar a tabela? Com base no enunciado do desafio, entendi que devo priorizar para manter as informações das tabelas migradas da forma mais fiel possível,
dentro do contexto no novo sistema, sem perder as informações que são do sistema legado.
*/

$sqlAlterTable = "ALTER TABLE pacientes ADD COLUMN nome_pai VARCHAR(255) NULL, ADD COLUMN nome_mae VARCHAR(255) NULL, ADD COLUMN obs_clinicas VARCHAR(255) NULL";
$connMedical->query($sqlAlterTable);

$sqlAlterTable = "INSERT INTO convenios (id, nome, descricao) VALUES (5, 'Hospital', 'Convênio do Hospital')";
$connMedical->query($sqlAlterTable);

                                                          /*------- INÍCIO MIGRAÇÃO TABELA PACIENTES -------*/

echo "1ª Parte da Migração - Tabela PACIENTES (com data até 12-05-2021).\n\n";

$csvFile = "dados_sistema_legado/20210512_pacientes.csv"; //Caminho do arquivo para leitura

$loadFile = fopen($csvFile, 'r');

if (!$loadFile) {
  die("Erro ao abrir o arquivo CSV.");
}


// Leitura da primeira linha com os cabeçalhos do arquivo CSV (nomes das colunas)
$csvHeader = fgetcsv($loadFile, 1000, ';');

// Mapeamento das colunas
$columnMapping = [
  'cod_paciente' => 'id',
  'nome_paciente' => 'nome',
  'sexo_pac' => 'sexo',
  'nasc_paciente' => 'nascimento',
  'cpf_paciente' => 'cpf',
  'rg_paciente' => 'rg',
  'id_conv' => 'id_convenio',
  'pai_paciente' => 'nome_pai',
  'mae_paciente' => 'nome_mae',
  'obs_clinicas' => 'obs_clinicas'
];

// Preparação da declaração SQL
$sql = "INSERT INTO pacientes (" . implode(", ", array_values($columnMapping)) . ") VALUES (?,?,?,?,?,?,?,?,?,?)";
$stmt = $connMedical->prepare($sql);
$stmt->bind_param("isssssisss", $id, $nome, $sexo, $nascimento, $cpf, $rg, $id_convenio, $nome_pai, $nome_mae, $obs_clinicas);

// Consulta para verificar se um valor já existe na coluna "id" da tabela "pacientes"
$verificaExistenciaQuery = $connMedical->prepare("SELECT id FROM pacientes WHERE id = ?");
$verificaExistenciaQuery->bind_param("i", $idPaciente);

// Leitura do restante do arquivo CSV
while (($data = fgetcsv($loadFile, 1000, ';')) !== false) {
  // Verificando existência do valor na tabela "pacientes"
  $idPaciente = $data[0];
  $verificaExistenciaQuery->execute();
  $verificaExistenciaQuery->store_result();

  if ($verificaExistenciaQuery->num_rows > 0) {
    // Se o valor já existe, substituir por um valor maior, a partir do 10278
    $data[0] = obterNovoID($connMedical);    
  }

  // Mapeamento dos dados do arquivo .CSV para o MySQL
  $id = (int) $data[array_search('cod_paciente', $csvHeader)];

  $nome = $data[array_search('nome_paciente', $csvHeader)];

  // Aqui, converto o sexo M e F para Masculino e Feminino, respectivamente
  $sexo = ($data[array_search('sexo_pac', $csvHeader)] == 'M') ? 'Masculino' : 'Feminino';

  // Aqui, eu converto a data de nascimento de acordo com o padrão do date do MySQL
  $nascimento = date("Y-m-d", strtotime($data[array_search('nasc_paciente', $csvHeader)]));

  $cpf = $data[array_search('cpf_paciente', $csvHeader)];

  $rg = $data[array_search('rg_paciente', $csvHeader)];

  $nome_pai = $data[array_search('pai_paciente', $csvHeader)];

  $nome_mae = $data[array_search('mae_paciente', $csvHeader)];

  // Aqui, converto os valores 2 e 3 para 1 e 4 respectivamente, pois os codigos não estão na tabela do MySQL do sistema novo
  $id_convenio = (int) ($data[array_search('id_conv', $csvHeader)] == 2) ? 1 : (($data[array_search('id_conv', $csvHeader)] == 3) ? 4 : $data[array_search('id_conv', $csvHeader)]);
  
  $obs_clinicas = $data[array_search('obs_clinicas', $csvHeader)];

  //var_dump($idPaciente);
  //var_dump($data[0]);
  //var_dump($id);

  // Execução da declaração preparada
  //var_dump($id, $nome, $sexo, $nascimento, $cpf, $rg, $id_convenio, $nome_pai, $nome_mae, $obs_clinicas);
  $stmt->execute();
}
// Fecha o arquivo CSV
fclose($loadFile);

$stmt->close();
                                                           /*------- FIM MIGRAÇÃO TABELA PACIENTES -------*/
                                                                                                               
                                                        /*------- INÍCIO MIGRAÇÃO TABELA AGENDAMENTOS -------*/

echo "2ª Parte da Migração - Tabela AGENDAMENTOS (com data até 12-05-2021).\n\n";

$csvFile = "dados_sistema_legado/20210512_agendamentos.csv"; //Caminho do arquivo para leitura

$loadFile = fopen($csvFile, 'r');

if (!$loadFile) {
  die("Erro ao abrir o arquivo CSV.");
}

// Leitura da primeira linha com os cabeçalhos do arquivo CSV (nomes das colunas)
$csvHeader = fgetcsv($loadFile, 1000, ';');

// Mapeamento das colunas
$columnMapping = [
  'cod_agendamento' => 'id',
  'cod_paciente' => 'id_paciente',
  'cod_medico' => 'id_profissional',
  'hora_inicio' => 'dh_inicio',
  'hora_fim' => 'dh_fim',
  'cod_convenio' => 'id_convenio',
  'procedimento' => 'id_procedimento',
  'descricao' => 'observacoes'
];

$sql = "INSERT INTO agendamentos (" . implode(", ", array_values($columnMapping)) . ") VALUES (?,?,?,?,?,?,?,?)";
$stmt = $connMedical->prepare($sql);
$stmt->bind_param("iiissiis", $id, $id_paciente, $id_profissional, $dh_inicio, $dh_fim, $id_convenio, $procedimento, $observacoes);

// Leitura do restante do arquivo CSV
while (($data = fgetcsv($loadFile, 1000, ';')) !== false) {

  // Substitui 'cod_agendamento' pela coluna 'id'
  $id = (int) $data[array_search('cod_agendamento', $csvHeader)];

  // Valida e corrige 'cod_paciente' conforme necessário
  $id_paciente = (int) validarIdPaciente($connMedical, $data[array_search('cod_paciente', $csvHeader)], $data[array_search('paciente', $csvHeader)]);

  // Valida e corrige 'cod_medico' conforme necessário
  $id_profissional = (int) ($data[array_search('cod_medico', $csvHeader)] == 1) ? 85218 : (($data[array_search('cod_medico', $csvHeader)] == 2) ? 85217 : $data[array_search('cod_convenio', $csvHeader)]);
    
  // Concatenação de 'dia' e 'hora_inicio' para 'dh_inicio' e conversão para formato datetime
  $dh_inicio = date("Y-m-d H:i:s", strtotime(($data[array_search('dia', $csvHeader)]) . ' ' . ($data[array_search('hora_inicio', $csvHeader)])));

  // Concatenação de 'dia' e 'hora_fim' para 'dh_fim' e conversão para formato datetime
  $dh_fim = date("Y-m-d H:i:s", strtotime(($data[array_search('dia', $csvHeader)]) . ' ' . ($data[array_search('hora_fim', $csvHeader)])));

  // Valida e corrige 'cod_convenio' conforme necessário
  $id_convenio = (int) ($data[array_search('cod_convenio', $csvHeader)] == 2) ? 1 : (($data[array_search('cod_convenio', $csvHeader)] == 3) ? 4 : $data[array_search('cod_convenio', $csvHeader)]);

  // Substituição do valor da coluna 'procedimento' pelo 'id' correspondente na tabela 'procedimentos'
  $procedimento = (int) obterIdProcedimento($connMedical, $data[array_search('procedimento', $csvHeader)]);;

  // Substitui 'descricao' pela coluna 'observacoes'
  $observacoes = $data[array_search('descricao', $csvHeader)];

  // Inserção dos dados na tabela 'agendamentos'
  //var_dump($id, $id_paciente, $id_profissional, $dh_inicio, $dh_fim, $id_convenio, $procedimento, $observacoes);
  $stmt->execute();
}

// Fecha o arquivo CSV
fclose($loadFile);

$stmt->close();
                                                          /*------- FIM MIGRAÇÃO TABELA AGENDAMENTOS -------*/

                                                     /*------- FIM CÓDIGO DESAFIO - ISRAEL RIBAS JACINTO -------*/

// Encerrando as conexões:
$connMedical->close();
$connTemp->close();

// Informações de Fim da Migração:
echo "Fim da Migração: " . dateNow() . ".\n";

?>