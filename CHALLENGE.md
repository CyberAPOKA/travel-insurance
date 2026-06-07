Teste Técnico - Desenvolvedor(a) Pleno ·
Laravel + React
Vaga: Full Stack Pleno · Root Code Tempo estimado: ~4 horas Stack obrigatória: Laravel 9+
(PHP 8+) no backend · ReactJS (Hooks) ou Next.js no frontend
Contexto
Trabalhamos com seguro viagem. Uma das peças centrais de qualquer operação de seguros é
o motor de cotação: dado um destino, um período e os viajantes, calcular o preço final
aplicando regras de precificação.
Seu desafio é construir uma versão enxuta desse motor: uma API em Laravel que recebe os
dados de uma viagem e devolve a cotação detalhada, e um frontend em React/Next que
monta a requisição e exibe o resultado.
O foco da avaliação é a lógica de precificação - não o visual. Capricho na UI é bem-vindo,
mas não vale tanto quanto uma lógica correta, bem organizada e testada.
O que você vai construir

1. API Laravel - POST /api/quotes
   Recebe um JSON descrevendo a viagem e retorna a cotação detalhada (ver formato adiante).
2. Frontend React/Next
   Um formulário para montar a viagem (destino, datas, add-ons e lista de viajantes) e uma tela
   que exibe a cotação detalhada (subtotal por viajante, avisos e total final).
   Regras de negócio (leia com atenção - está tudo aqui)
   Todos os valores são em BRL. Os números abaixo são fictícios e fazem parte do teste.
   Período da viagem (dias cobrados)
   ● dias = (data_fim − data_inicio) + 1 - ambos os dias contam. Uma viagem de 01/06 a
   01/06 cobre 1 dia.
   ● Período mínimo cobrado: 5 dias. Se a viagem for mais curta, cobra-se 5 dias mesmo
   assim.
   ● data_fim deve ser maior ou igual a data_inicio. Caso contrário, é erro de validação.
   Tarifa diária por zona de destino (por viajante)
   Zona
   NACIONAL
   AMERICAS
   R$ 10,00
   Tarifa/dia
   R$ 16,00
   EUROPA
   R$ 22,00
   Faixa etária (multiplicador, por viajante)
   A idade é calculada na data de início da viagem (não na data de hoje).
   Idade na data de início
   0 a 17
   18 a 64
   ×0,5
   Multiplicador
   ×1,0
   65 ou mais
   Adicionais (add-ons, por viajante)
   ×2,0
   ● BAGAGEM - extravio de bagagem: + R$ 3,00 × dias cobrados.
   ● ESPORTES_AVENTURA - esportes radicais: acrescenta 25% sobre o subtotal
   daquele viajante (subtotal = tarifa × dias × idade, antes do add-on de bagagem). Só é
   permitido para viajantes de 18 a 64 anos. Se for solicitado para um viajante fora
   dessa faixa, o adicional não é aplicado para ele, mas a cotação deve retornar um aviso
   (não é erro - a cotação segue normalmente).
   Desconto de grupo (sobre o total, depois de somar todos os viajantes)
   Nº de viajantes
   1 a 4
   5 ou mais
   Desconto
   0%
   10%
   Ordem de cálculo
   Para cada viajante:
   JSON
   base  
   subtotal  
   = tarifa_zona × dias_cobrados
   = base × multiplicador_idade
   se ESPORTES_AVENTURA (e elegível): subtotal += subtotal × 0,25
   se BAGAGEM:  
   Depois:
   JSON
   subtotal += 3,00 × dias_cobrados
   total_grupo = soma dos subtotais de todos os viajantes
   total_final = total_grupo − (total_grupo × desconto_grupo)
   Arredondamento
   ● Mantenha precisão total nos cálculos intermediários.
   ● Arredonde somente o total final para 2 casas decimais, usando arredondamento
   meio-para-cima (half-up).
   ● O subtotal por viajante pode ser arredondado para 2 casas só na apresentação, mas a
   soma usada no total deve usar os valores não arredondados.
   Formato sugerido da requisição
   JSON
   {
   }
   "destino": "EUROPA",
   "data_inicio": "2026-07-10",
   "data_fim": "2026-07-20",
   "viajantes": [
   {
   "nome": "Ana",
   "data_nascimento": "1990-03-15",
   "adicionais": ["BAGAGEM", "ESPORTES_AVENTURA"]
   },
   {
   "nome": "João",
   "data_nascimento": "1948-11-02",
   "adicionais": ["ESPORTES_AVENTURA", "BAGAGEM"]
   }
   ]
   Formato sugerido da resposta
   JSON
   {
   "dias_cobrados": 11,
   "viajantes": [
   {
   "nome": "Ana",
   "idade": 36,
   "subtotal": 0.00,
   "adicionais_aplicados": ["BAGAGEM", "ESPORTES_AVENTURA"]
   },
   {
   "nome": "João",
   "idade": 77,
   "subtotal": 0.00,
   "adicionais_aplicados": ["BAGAGEM"]
   }
   ],
   "avisos": [
   "ESPORTES_AVENTURA não aplicado para João: fora da faixa etária permitida
   (18-64)."
   ],
   "desconto_grupo_percentual": 0,
   "total_final": 0.00
   }
   Você pode ajustar nomes de campos e estrutura, desde que a resposta deixe claro: o
   subtotal por viajante, os avisos, o desconto aplicado e o total final.

Requisitos obrigatórios

1. Service isolado de precificação. A lógica de cálculo deve viver em uma classe/serviço
   dedicado, separada do controller. O controller só orquestra (valida entrada, chama o
   serviço, devolve resposta).
2. Validação de entrada no backend (datas, zona e viajantes válidos).
3. Testes unitários (PHPUnit) cobrindo o serviço de precificação. Queremos ver, no
   mínimo, testes para: período mínimo de 5 dias, cálculo de idade na data de início,
   esportes de aventura negado com aviso, desconto de grupo, e ao menos um cenário
   completo com múltiplos viajantes e add-ons. Escreva os testes você mesmo,
   derivando os valores esperados na mão.
4. Frontend funcional consumindo a API, com gerenciamento de estado (Context,
   Zustand ou Redux - sua escolha) e exibição da cotação detalhada.
5. README com:
   ○ Como rodar o backend e o frontend.
   ○ Uma seção "Decisões e premissas" explicando suas escolhas técnicas e
   qualquer ambiguidade que você encontrou nas regras e como resolveu.
   Diferenciais (não obrigatórios)
   ● Persistência das cotações em banco (MySQL ou PostgreSQL) com endpoint para listar
   cotações salvas.
   ● Docker / docker-compose para subir o projeto.
   ● Tratamento de erros e estados de loading no frontend.
   Não priorize os diferenciais antes de o obrigatório estar sólido. Preferimos o
   núcleo lógico correto e testado a um projeto cheio de extras com cálculo errado.
   Entrega
   ● Repositório Git (GitHub/GitLab) público ou com acesso liberado, com histórico de
   commits - não envie um único commit gigante.
   ● Inclua o README com instruções de execução e a seção de decisões.
   ● Se houver algo que você não terminou, descreva no README o que faltou e como
   abordaria.
