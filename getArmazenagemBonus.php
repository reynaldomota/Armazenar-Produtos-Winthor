<?php

function getArmazenagemBonus($numbonus) {
    header('Content-Type: application/json; charset=UTF-8');
    $bonus = filter_var($numbonus, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$bonus) {
        echo json_encode(['erro' => true, 'msg' => 'Informe um NUMBONUS válido.']);
        return;
    }

    require __DIR__ . '/config/conexao.php';
    $stmt = null;
    try {
        $stmt = oci_parse($conn, 'SELECT arm.codseparador1, arm.codseparador2,
                arm.codseparador3, arm.codseparador4,
                emp1.nome AS nome1, emp2.nome AS nome2,
                emp3.nome AS nome3, emp4.nome AS nome4
            FROM iwbonusarmazenar arm
            LEFT JOIN pcempr emp1 ON emp1.matricula = arm.codseparador1
            LEFT JOIN pcempr emp2 ON emp2.matricula = arm.codseparador2
            LEFT JOIN pcempr emp3 ON emp3.matricula = arm.codseparador3
            LEFT JOIN pcempr emp4 ON emp4.matricula = arm.codseparador4
            WHERE arm.numbonus = :bonus
                AND (arm.dtarmazenagemfin IS NOT NULL OR arm.dtarmazenagem IS NOT NULL)');
        if (!$stmt) throw new RuntimeException('Não foi possível consultar a armazenagem.');
        oci_bind_by_name($stmt, ':bonus', $bonus);
        if (!oci_execute($stmt)) throw new RuntimeException('Não foi possível consultar a armazenagem.');

        $armazenado = false;
        $responsaveis = [];
        while ($row = oci_fetch_array($stmt, OCI_ASSOC | OCI_RETURN_NULLS)) {
            $armazenado = true;
            for ($i = 1; $i <= 4; $i++) {
                $codigo = $row['CODSEPARADOR' . $i];
                if ($codigo === null) continue;
                $responsaveis[$codigo] = [
                    'codigo' => (string) $codigo,
                    'nome' => $row['NOME' . $i] ?: 'Nome não localizado'
                ];
            }
        }
        ksort($responsaveis, SORT_NUMERIC);
        echo json_encode(['erro' => false, 'armazenado' => $armazenado,
            'responsaveis' => array_values($responsaveis)]);
    } catch (Exception $erro) {
        echo json_encode(['erro' => true, 'msg' => $erro->getMessage()]);
    } finally {
        if ($stmt) oci_free_statement($stmt);
        oci_close($conn);
    }
}
