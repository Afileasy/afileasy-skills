# Afileasy skills

Skills que ensinam agents de código (Claude Code, Cursor, Codex e outros) a integrar uma loja, um checkout ou um gateway de pagamento ao [Afileasy](https://afileasy.com).

*English below.*

## O que a skill `afileasy-integrations` faz

- Descobre pelo código qual plataforma você usa e diz se ela é suportada.
- Mostra o que fazer no painel do Afileasy e no painel do provedor.
- Instala o script de rastreamento e envia o código do afiliado na criação da cobrança.
- Registra leads quando você quiser.
- Confere a atribuição e ajuda a descobrir por que uma venda não chegou ao afiliado.

**Provedores suportados:** Stripe, Asaas, AbacatePay, WooCommerce, Shopify e Nuvemshop.

O agent não acessa o seu painel. Os passos de lá ficam com você, e as chaves e os segredos não passam pelo chat.

## Instalação

**Claude Code**

```text
/plugin marketplace add Afileasy/afileasy-skills
/plugin install afileasy@afileasy
```

**Outros agents** (CLI [skills](https://github.com/vercel-labs/skills))

```bash
npx skills add Afileasy/afileasy-skills
```

**Manual:** copie `skills/afileasy-integrations/` para a pasta de skills do seu agent, por exemplo `.claude/skills/` no seu projeto.

Depois, peça ao agent, por exemplo: *"integra o Afileasy no meu checkout do Stripe"* ou *"por que a venda não apareceu para o afiliado?"*.

---

## English

The `afileasy-integrations` skill teaches coding agents to connect a store, checkout or payment gateway to Afileasy. It detects your platform, explains the dashboard steps, installs the tracking script, stamps the affiliate code on charges, sets up lead capture and verifies attribution.

Supported providers: Stripe, Asaas, AbacatePay, WooCommerce, Shopify and Nuvemshop.

Install with `/plugin marketplace add Afileasy/afileasy-skills` and then `/plugin install afileasy@afileasy` in Claude Code, or with `npx skills add Afileasy/afileasy-skills`.

## Layout

```
.claude-plugin/          plugin + marketplace manifests
skills/afileasy-integrations/
  SKILL.md               workflow, provider table, ground rules
  references/            one file per provider + script, leads, troubleshooting
  evals/evals.json       test prompts used to evaluate the skill
```
