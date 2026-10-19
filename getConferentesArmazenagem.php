<?php

function getConferentesArmazenagem($itens) {
    header('Content-Type: application/json; charset=UTF-8');
    require __DIR__ . '/config/conexao.php';
    $conferentes = [];
    try {
        foreach ($itens as $item) {
            $bonus = filter_var($item['numbonus'], FILTER_VALIDATE_INT);
            $produto = filter_var($item['codprod'], FILTER_VALIDATE_INT);
            if (!$bonus || !$produto) throw new RuntimeException('Item inválido.');
            $stmt = oci_parse($conn, 'SELECT DISTINCT emp.matricula, emp.nome
                FROM iwbonusarmazenar arm
                INNER JOIN pcempr emp ON emp.matricula IN
                    (arm.codseparador1, arm.codseparador2, arm.codseparador3, arm.codseparador4)
                WHERE arm.numbonus = :bonus AND arm.codprod = :produto
                AND arm.dtarmazenagemini IS NOT NULL
                ORDER BY emp.matricula');
            if (!$stmt) throw new RuntimeException('Não foi possível carregar os conferentes.');
            oci_bind_by_name($stmt, ':bonus', $bonus);
            oci_bind_by_name($stmt, ':produto', $produto);
            if (!oci_execute($stmt)) throw new RuntimeException('Não foi possível carregar os conferentes.');
            while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                $conferentes[$row['MATRICULA']] = ['codigo' => (string) $row['MATRICULA'], 'nome' => $row['NOME']];
            }
            oci_free_statement($stmt);
        }
        echo json_encode(['erro' => false, 'conferentes' => array_values($conferentes)]);
    } catch (Exception $erro) {
        echo json_encode(['erro' => true, 'msg' => $erro->getMessage()]);
    }
}
