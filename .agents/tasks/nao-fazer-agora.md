# 🚫 Não fazer agora — Funcionalidades congeladas

> **Tipo:** referência (não é executável). Agentes **não devem implementar** nada desta lista, mesmo que pareça simples ou "já esteja quase pronto".
> **Por quê (Lean):** nenhuma destas funcionalidades ajuda a validar a hipótese principal — *"profissionais usam um link de agendamento online e os clientes deles reservam por ali"*. Construí-las agora consome semanas e adia o aprendizado real.

## Regra de descongelamento

Um item só sai desta lista quando houver **aprendizado validado** no piloto ([metricas-e-validacao.md](metricas-e-validacao.md)):
- pedido espontâneo de **≥ 2 profissionais** diferentes, **ou**
- evidência de que a ausência **impede** o uso (ex.: profissional abandonou por causa disso).

Ao descongelar: mover o item para uma nova task em `.agents/tasks/` (com o mesmo formato: problema, sugestão, perguntas-chave, critérios de aceite) e registrar abaixo a data e o motivo.

## Lista

| Funcionalidade | Por que esperar |
|----------------|-----------------|
| **Geolocalização / busca por proximidade** | Só faz sentido com marketplace e muitos estabelecimentos; o tráfego do MVP vem do link do próprio profissional. |
| **Avaliações pós-serviço** | Sem volume de atendimentos, não há avaliações suficientes para gerar valor. |
| **Seleção guiada de profissional / equipes** | O produto ainda não permite criar estabelecimento com vários profissionais (ver item 3.3 de [arquitetura-e-estado.md](arquitetura-e-estado.md)). |
| **Planos e pacotes de serviços** | Complexidade de regra de negócio sem demanda comprovada. |
| **Programa de fidelidade** | Retenção do cliente final vem depois de provar a adoção pelo profissional. |
| **PWA (manifest, service worker)** | A página web responsiva já cobre o uso pelo link. |
| **Push / SMS / lembretes automáticos (D-1)** | O MVP cobre só e-mail transacional + botão WhatsApp (item 2.3 de [fluxo-de-reserva-ux.md](fluxo-de-reserva-ux.md)). |
| **`relatorios_financeiros` pré-processados (jobs)** | Volume do piloto é ínfimo; queries diretas bastam. |
| **Status `pendente` com aprovação manual** | O profissional quer menos trabalho, não mais; confirmação automática é suficiente. |
| **Melhorias na busca/listagem do marketplace e na Home** | O canal de aquisição do MVP é o link direto, não a descoberta na plataforma. |
| **Fuso horário por estabelecimento** | Piloto é local; fuso fixo `America/Sao_Paulo` resolve. |
| **Fusão de contas (pré-cadastro × conta criada pelo cliente)** | Caso de borda; tratar manualmente se ocorrer. |
| **CI/CD, testes E2E (Playwright), testes de front** | Testes de feature no backend + deploy por script bastam para o piloto. |
| **Novas cores/temas, dark/light toggle, refinamentos visuais** | Branding por estabelecimento já está pronto; refinamento visual não muda a hipótese. |
| **Pagamento online / sinal antecipado** | Só avaliar se no-show aparecer como dor real nos check-ins do piloto. |

## Histórico de descongelamentos

| Data | Funcionalidade | Evidência | Task criada |
|------|----------------|-----------|-------------|
| — | — | — | — |
