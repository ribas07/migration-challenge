<?php
/*
  Biblioteca de Funções.
    Você pode separar funções muito utilizadas nesta biblioteca, evitando replicação de código.
*/

function dateNow(){
  date_default_timezone_set('America/Sao_Paulo');
  return date('d-m-Y \à\s H:i:s');
}

// Função para obter um novo ID para substituir valores existentes para importar na coluna "id" dentro da tabela "pacientes"
function obterNovoID($connMedical) {
  $consultaUltimoID = $connMedical->query("SELECT MAX(id) AS ultimo_id FROM pacientes");
  $ultimoID = $consultaUltimoID->fetch_assoc()['ultimo_id'];
  $novoID = ($ultimoID >= 10278) ? $ultimoID + 1 : 10278;
  $consultaUltimoID->close();
  return $novoID;
}


// Função para obter o ID do procedimento da tabela "procedimentos" para importar na coluna "id_procedimentos" dentro da tabela "agendamentos"
function obterIdProcedimento($connMedical, $procedimentoNome) {
  $sql = "SELECT id FROM procedimentos WHERE nome = '$procedimentoNome'";
  $result = $connMedical->query($sql);

  if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      return $row['id'];
  } else {      
    echo "Erro: não há procedimentos cadastrados na tabela 'procedimentos' para inserção das informações na coluna 'id_procedimento'!";
  }
}

// Função para validar e corrigir 'cod_paciente'
function validarIdPaciente($connMedical, $cod_paciente, $paciente) {
  $sql = "SELECT id FROM pacientes WHERE nome = '$paciente'";
  $result = $connMedical->query($sql);

  if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      return $row['id'];
  } else {
      return $cod_paciente;
  }
}