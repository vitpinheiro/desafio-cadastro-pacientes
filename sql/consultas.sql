-- Execute as consultas no mesmo banco configurado no .env da aplicação.

-- 1. Listar os pacientes e o nome da respectiva unidade de saúde.
SELECT
    patients.name AS nome_paciente,
    health_units.name AS unidade_saude
FROM patients
INNER JOIN health_units ON health_units.id = patients.health_unit_id
ORDER BY patients.name;

-- 2. Contar os pacientes por unidade, incluindo unidades sem pacientes.
-- COUNT(patients.id) retorna zero quando o LEFT JOIN não encontra pacientes.
SELECT
    health_units.id,
    health_units.name AS unidade_saude,
    COUNT(patients.id) AS total_pacientes
FROM health_units
LEFT JOIN patients ON patients.health_unit_id = health_units.id
GROUP BY health_units.id, health_units.name
ORDER BY health_units.name;

-- 3. Buscar pacientes cujo nome contenha o termo informado.
-- Altere apenas o valor de @termo para fazer outra busca.
SET @termo = 'Maria';

SELECT
    patients.id,
    patients.name,
    patients.cpf,
    patients.birth_date,
    patients.phone,
    patients.health_unit_id
FROM patients
WHERE patients.name LIKE CONCAT('%', @termo, '%')
ORDER BY patients.name;
