<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dados Migrados - Medical Challenge</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: center;
        }

        section {
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #333;
            color: white;
        }
    </style>
</head>

<body>
    <header>
        <h1>Dados Migrados - Medical Challenge</h1>
    </header>

    <section>
        <h2>Tabela de Pacientes</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Sexo</th>
                    <th>Nascimento</th>
                    <th>CPF</th>
                    <th>RG</th>
                    <th>Convenio</th>
                    <th>Pai</th>
                    <th>Mãe</th>
                    <th>Observações Clínicas</th>
                </tr>
            </thead>
            <tbody>
                <!-- Loop para exibir dados da tabela de Pacientes -->
                <?php
                // Conexão com o banco de dados
                $conn = new mysqli("localhost", "root", "root", "MedicalChallenge");

                // Verifica se a conexão foi estabelecida com sucesso
                if ($conn->connect_error) {
                    die("Conexão falhou: " . $conn->connect_error);
                }

                // Consulta SQL para buscar dados da tabela de Pacientes
                $sql = "SELECT * FROM pacientes";
                $result = $conn->query($sql);

                // Exibição dos dados
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['nome']}</td>
                                <td>{$row['sexo']}</td>
                                <td>{$row['nascimento']}</td>
                                <td>{$row['cpf']}</td>
                                <td>{$row['rg']}</td>
                                <td>{$row['id_convenio']}</td>
                                <td>{$row['nome_pai']}</td>
                                <td>{$row['nome_mae']}</td>
                                <td>{$row['obs_clinicas']}</td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>Nenhum registro encontrado.</td></tr>";
                }

                // Fecha a conexão com o banco de dados
                $conn->close();
                ?>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Tabela de Agendamentos</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID Paciente</th>
                    <th>ID Profissional</th>
                    <th>Data/Hora Início</th>
                    <th>Data/Hora Fim</th>
                    <th>ID Convênio</th>
                    <th>ID Procedimento</th>
                    <th>Observações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Loop para exibir dados da tabela de Agendamentos -->
                <?php
                // Conexão com o banco de dados
                $conn = new mysqli("localhost", "root", "root", "MedicalChallenge");

                // Verifica se a conexão foi estabelecida com sucesso
                if ($conn->connect_error) {
                    die("Conexão falhou: " . $conn->connect_error);
                }

                // Consulta SQL para buscar dados da tabela de Agendamentos
                $sql = "SELECT * FROM agendamentos";
                $result = $conn->query($sql);

                // Exibição dos dados
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['id_paciente']}</td>
                                <td>{$row['id_profissional']}</td>
                                <td>{$row['dh_inicio']}</td>
                                <td>{$row['dh_fim']}</td>
                                <td>{$row['id_convenio']}</td>
                                <td>{$row['id_procedimento']}</td>
                                <td>{$row['observacoes']}</td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>Nenhum registro encontrado.</td></tr>";
                }

                // Fecha a conexão com o banco de dados
                $conn->close();
                ?>
            </tbody>
        </table>
    </section>
</body>

</html>