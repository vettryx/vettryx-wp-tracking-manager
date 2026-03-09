# VETTRYX WP Tracking Manager

> ⚠️ **Atenção:** Este repositório atua exclusivamente como um **Submódulo** do ecossistema principal `VETTRYX WP Core`. Ele não deve ser instalado como um plugin standalone (isolado) nos clientes.

Este submódulo é um gerenciador nativo e inteligente para injeção de IDs de rastreamento (Google Tag Manager, GA4, Meta Pixel) diretamente no WordPress. Ele elimina a dependência de plugins de terceiros, otimiza o carregamento das tags e evita que clientes quebrem o rastreamento acidentalmente.

## 🚀 Funcionalidades

* **Injeção Inteligente (IDs):** Em vez de colar blocos pesados de código, basta inserir o ID (ex: `GTM-XXXXXXX`) e o plugin monta e injeta os scripts otimizados do Google e do Meta nos locais corretos (`<head>` e `<body>`).
* **Segurança e Blindagem:** Apenas usuários com capacidade de administrador (`manage_options`) podem visualizar a interface e salvar as configurações. Os IDs são estritamente higienizados contra injeções.
* **Flexibilidade (Fallback):** Possui um campo extra blindado para "Scripts Customizados", permitindo a injeção de tags brutas (`<script>`, `<iframe>`) caso uma ferramenta de terceiros não suporte o GTM.
* **Alta Performance:** Não processa shortcodes e injeta o código via ganchos nativos, garantindo que o tempo de carregamento do front-end do cliente não seja impactado.
* **White-Label:** Fica encapsulado silenciosamente dentro do menu "VETTRYX Tech" no painel do cliente.

## ⚙️ Arquitetura e Deploy (CI/CD)

Este repositório não gera mais arquivos `.zip` para instalação manual. O fluxo de deploy é 100% automatizado:

1. Qualquer push na branch `main` deste repositório dispara um webhook (Repository Dispatch) para o repositório principal do Core.
2. O repositório do Core puxa este código atualizado para dentro da pasta `/modules/`.
3. O GitHub Actions do Core empacota tudo e gera uma única Release oficial.

## 📖 Como Usar

Uma vez que o **VETTRYX WP Core** esteja instalado e o módulo Tracking Manager ativado no painel do cliente:

1. No menu lateral do WordPress, acesse **VETTRYX Tech > Tracking Manager**.
2. Preencha os campos com os respectivos IDs de rastreamento (Recomendação: utilize apenas o Google Tag Manager ID para centralizar suas tags).
3. Salve as alterações. Os scripts reais serão construídos e injetados automaticamente no front-end do site de forma invisível.

---

**VETTRYX Tech**
*Transformando ideias em experiências digitais.*
