# Uso de Inteligência Artificial no desenvolvimento

Este documento descreve, de forma breve, como a IA foi utilizada na construção do projeto **Gestão Financeira — Contas a Receber**.

## Ferramenta

Foi utilizado um assistente de código com IA (OpenCode) como par de programação durante o desenvolvimento, combinando geração de código, revisão e depuração. A IA foi tratada como ferramenta de produtividade: cada sugestão foi lida, adaptada e testada antes de entrar no projeto.

## Etapas em que a IA foi usada

1. **Planejamento**
   - Interpretação do escopo do teste.
   - Definição do modelo de dados `contas_receber`.
   - Discussão das regras de negócio, especialmente o status **Vencido**.

2. **Backend**
   - Estruturação das rotas, controllers, model e seeder.
   - Implementação dos filtros e da paginação.
   - Criação da ação de registrar recebimento.
   - Parsers de importação CSV/OFX.
   - Middleware de log das requisições.

3. **Frontend**
   - Dashboard com indicadores, gráfico e listas de apoio.
   - Listagem, pesquisa e filtros.
   - Modais de cadastro, edição, recebimento e exclusão.
   - Ajustes de responsividade e compactação para 1920×1080.

4. **Qualidade e depuração**
   - Correção de IDs duplicados entre modais.
   - Correção de vazamento de variável de loop do Blade que preenchia o modal de cadastro com dados da última linha.
   - Reabertura do modal correto após erro de validação.
   - Conferência de totais entre dashboard, filtros e banco.
   - Revisão de código morto e remoção de um CRUD de produtos que não fazia parte do escopo.

5. **Documentação**
   - Estrutura do README.
   - Instruções de execução e de dados fictícios.
   - Este relato de uso de IA.

## Exemplos de prompts utilizados

- “Como implementar o status Vencido de forma derivada, sem precisar atualizar o banco todos os dias?”
- “Crie um modal de exclusão somente leitura reutilizando o layout do modal de edição.”
- “Ajuste o dashboard para caber em 1920×1080 sem rolagem de página.”
- “Por que o modal de cadastro está abrindo preenchido com os dados da última conta listada?”
- “Revise os filtros para preservar a query string na paginação e no formulário de busca.”

## O que foi revisado manualmente

- Regras de negócio e definição dos quatro indicadores do dashboard.
- Todos os fluxos no navegador: criar, editar, receber, excluir, filtrar e importar.
- Validação de formulários e mensagens de erro.
- Consistência dos valores exibidos com os registros do banco.
- Organização do código, nomes e remoção de arquivos fora do escopo.
- Ajustes visuais e de responsividade.

## Cuidados adotados

- Não aceitar código gerado sem leitura e teste.
- Validar toda entrada do usuário no backend.
- Manter as regras de negócio explícitas no código e na documentação.
- Evitar dependências desnecessárias para o escopo do teste.
- Conferir se os dados de demonstração permanecem consistentes após os testes.
- Não versionar `.env`, logs, caches, dumps ou chaves; o `.gitignore` e o `docs/SEGURANCA.md` documentam essa proteção.
- Não exibir detalhes internos de exceções ao usuário final.

## Conclusão

A IA acelerou a implementação e ajudou a encontrar problemas que seriam mais lentos de diagnosticar manualmente. As decisões de negócio, a validação funcional e a aprovação final de cada alteração permaneceram humanas.
