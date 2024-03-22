<div align="center">

  <h1>Sistema de Entrada de Mercadoria Integrado ao Winthor da TOTVs</h1>
  <p>Este sistema é integrado ao Winthor da TOTVs e puxa os dados adicionados da rotina 1106 (PCBONUS, PCBONUSI).</p>

  <img src="https://img.shields.io/badge/-HTML-24c308?style=flat&logo=html5&logoColor=white" alt="HTML" />
  <img src="https://img.shields.io/badge/-CSS-00b593?style=flat&logo=css3&logoColor=white" alt="css" />
   <img src="https://img.shields.io/badge/-JavaScript-dfb900?style=flat&logo=javascript&logoColor=white" alt="Javascript" />
  <img src="https://img.shields.io/badge/-PHP-4F5B93?style=flat&logo=php&logoColor=white" alt="php" />
  <img src="https://img.shields.io/badge/-Oracle-F80000?style=flat&logo=oracle&logoColor=white" alt="Oracle" />
 
</div>

<br>

![Minha animação](./src/_img/entrada.gif)

### Funcionalidades
- Visualização em tempo real das equipes de EMBALAGEM, CONFERÊNCIA e ARMAZENAGEM.
- Exibição em tempo real dos bônus que estão sendo feitos, com contagem de tempo.
- Possibilidade de fazer uma venda antecipada de um item que ainda não foi conferido no bônus:
- Adicione o item na rotina 1117.
- Coloque no campo de observação a tag: VA [NÚMERO DO BÔNUS] OBS.
    - Exemplo: VA 145612 VENDA FEITA PELA MAGDA.
- O sistema captura este evento e sinaliza a quantidade vendida, permitindo que a equipe de armazenagem saiba que este item não está mais no estoque.

</br>



### Funções

- Permite que o vendedor e todos da empresa saibam quais produtos estão entrando nas lojas.
- Facilita o aviso aos clientes sobre a disponibilidade de produtos.
- Filtro por data inicial e final. (default -15 dias)
- Filtro por: Disponível, Conferência, Sem Conferencia, Todos

</br>

### Armazenar Bonus
Tenha controle dos itens armazenados no estoque após o a conferencia da equipe de entrada (1106)
![Minha animação](./src/_img/tela-armazenar.png)

</br>




##### Criação de Tabela
É necessário criar a tabela IWBONUSARMAZENAR no Oracle para o sistema possa gravar a data de armazenazem e os separadores.


>> 
    CREATE TABLE iwbonusarmazenar (
        dtarmazenagem DATE,
        codprod NUMBER,
        numbonus NUMBER,
        codseparador1 NUMBER,
        codseparador2 NUMBER,
        codseparador3 NUMBER,
        codseparador4 NUMBER
    );

</br>


##### Criação de Trigger
Essa trigger deve ser criada na table PCBONUSI e ao realizar o checkout na rotina 1106 ele captura os dados necessários

>>
    CREATE OR REPLACE TRIGGER trg_insert_update_iwbonusi
    AFTER INSERT OR UPDATE OR DELETE ON PCBONUSI
    FOR EACH ROW
    BEGIN
        IF INSERTING OR UPDATING THEN
            BEGIN
            
                UPDATE iwbonusi
                SET codconferentes = :NEW.codagregacao,
                    dtinicioemb = CASE WHEN :NEW.codagregacao IS NOT NULL AND dtinicioemb IS NULL THEN SYSDATE ELSE dtinicioemb END
                WHERE numbonus = :NEW.numbonus
                AND codprod = :NEW.codprod;

                IF SQL%NOTFOUND THEN
                    INSERT INTO iwbonusi (numbonus, codprod, dtinicioemb, codconferentes)
                    VALUES (:NEW.numbonus, :NEW.codprod, CASE WHEN :NEW.codagregacao IS NOT NULL THEN SYSDATE END, :NEW.codagregacao);
                END IF;
            END;

            IF :NEW.qtentrada = 1 AND :NEW.qtentrada <> :NEW.qtnf THEN
                UPDATE iwbonusi
                SET dtinicioconf = SYSDATE
                WHERE numbonus = :NEW.numbonus
                AND codprod = :NEW.codprod
                AND dtinicioconf IS NULL;
            END IF;

            IF :NEW.qtentrada = :NEW.qtnf THEN
                UPDATE iwbonusi
                SET dtfinalconf = SYSDATE
                WHERE numbonus = :NEW.numbonus
                AND codprod = :NEW.codprod;
            END IF;
        END IF;

        IF :NEW.qtentrada = 0 AND :NEW.qtavaria = 0 AND :NEW.qtentun = 0 AND :NEW.qtentcx = 0 
            AND :NEW.qtavariaun = 0 AND :NEW.qtavariacx = 0 
            AND :NEW.dtvalidade IS NULL AND :NEW.codmotivo = 0  THEN
            DELETE FROM iwbonusi
            WHERE numbonus = :NEW.numbonus;
        END IF;
    END;



</br>

#### Tecnologias usadas no projeto
- PHP
- HTML / CSS / JAVASCRIPT
- Jquery
  