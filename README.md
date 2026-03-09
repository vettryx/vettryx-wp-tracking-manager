# VETTRYX WP Tracking Manager

> ⚠️ **Atenção:** Este repositório atua exclusivamente como um **Submódulo** do ecossistema principal `VETTRYX WP Core`. Ele não deve ser instalado como um plugin standalone (isolado) nos clientes.

Este submódulo é um gerenciador nativo e blindado para injeção de scripts de marketing e rastreamento (Google Analytics, Google Tag Manager, Meta Pixel, TikTok, etc.) diretamente no WordPress, eliminando a dependência de plugins de terceiros e evitando exclusões acidentais pelos clientes.

## 🚀 Funcionalidades

* **Injeção Estratégica:** Permite a inserção de códigos brutos diretamente no `<head>`, logo após a abertura do `<body>` e no `<footer>`.
* **Segurança e Blindagem:** Apenas usuários com capacidade de administrador (`manage_options`) podem visualizar a interface e salvar os scripts. Não há higienização agressiva que quebre tags `<script>` ou `<iframe>`.
* **Alta Performance:** Não processa shortcodes, garantindo que o tempo de carregamento do front-end do cliente não seja impactado.
* **White-Label:** Fica encapsulado silenciosamente dentro do menu "VETTRYX Tech" no painel do cliente.

## ⚙️ Arquitetura e Deploy (CI/CD)

Este repositório não gera mais arquivos `.zip` para instalação manual. O fluxo de deploy é 100% automatizado:

1. Qualquer push na branch `main` deste repositório dispara um webhook (Repository Dispatch) para o repositório principal do Core.
2. O repositório do Core puxa este código atualizado para dentro da pasta `/modules/`.
3. O GitHub Actions do Core empacota tudo e gera uma única Release oficial.

## 📖 Como Usar

Uma vez que o **VETTRYX WP Core** esteja instalado e o módulo Tracking Manager ativado no painel do cliente:

1. No menu lateral do WordPress, acesse **VETTRYX Tech > Tracking Scripts**.
2. Cole os seus códigos de rastreamento completos nos campos correspondentes (incluindo as tags HTML).
3. Salve as alterações. Os scripts serão injetados automaticamente no front-end do site de forma invisível.

---

**VETTRYX Tech**
*Transformando ideias em experiências digitais.*
