# MindCare: Plataforma Integrada de Saúde Mental

<p align="center">
  <img src="https://img.shields.io/badge/Status-Em%20Desenvolvimento-yellowgreen" alt="Status do Projeto">
  <img src="https://img.shields.io/badge/Linguagens-HTML%20%7C%20CSS%20%7C%20JS%20%7C%20PHP-blue" alt="Tecnologias Usadas">
  <img src="https://img.shields.io/badge/Licença-MIT-blue" alt="Licença">
</p>

## Visão Geral do Projeto

MindCare é uma plataforma abrangente e segura dedicada à promoção da **saúde mental** e ao **bem-estar digital**. Nosso objetivo é oferecer um espaço integrado onde usuários possam buscar apoio profissional, registrar sentimentos e acessar recursos educativos essenciais para enfrentar desafios modernos, como o combate ao suicídio, cyberbullying e os impactos da vida crônica online.

## Funcionalidades Principais

| Módulo | Emoji | Descrição |
| :--- | :---: | :--- |
| **Atendimento Profissional** | | Conexão direta para atendimento com profissionais de saúde mental (psicólogos, terapeutas, etc.). |
| **Diário Emocional** | | Ferramenta para o usuário registrar e acompanhar seus sentimentos e estados emocionais ao longo do tempo. |
| **Base de Conhecimento** | | Conteúdo educativo sobre saúde mental, mecanismos de enfrentamento e temas como suicídio e cyberbullying. |
| **Apoio Digital** |  | Foco na conscientização e combate aos problemas causados pela hiperconexão e vida online crônica. |
| **Painéis Dedicados** |  | Interfaces distintas para **Usuários Comuns** e **Profissionais** (dashboards dedicados). |

## Arquitetura e Tecnologias

O MindCare foi desenvolvido utilizando uma arquitetura web tradicional (Frontend/Backend), focada em simplicidade, desempenho e portabilidade.

### Frontend (Camada de Apresentação)

| Tecnologia | Arquivos | Propósito |
| :--- | :--- | :--- |
| **HTML** | `index.html`, `dashboard-user.html`, `dashboard-pro.html`, etc. | Estrutura e marcação de conteúdo. |
| **CSS** | `styles.css` | Estilização, design responsivo e layout. |
| **JavaScript** | `script.js` | Lógica de interação do lado do cliente e validações. |

### Backend (Processamento e Dados)

| Tecnologia | Arquivo | Propósito |
| :--- | :--- | :--- |
| **PHP** | `Allin.php` | Lógica de servidor, processamento de autenticação e manipulação de dados. |
| **MySQL (Implícito)**| N/A | Banco de dados relacional para persistência de `Usuario`, `Humor` e `Conteudo`. |

## Processo de Criação e Desenvolvimento

O desenvolvimento do MindCare seguiu uma abordagem modular:

1.  **Modelagem e Estruturação:** Criação das páginas estáticas essenciais e distinção clara entre os painéis de usuário (`dashboard-user.html`) e profissional (`dashboard-pro.html`).
2.  **Design e Usabilidade:** Aplicação de estilos globais em `styles.css`, priorizando a acessibilidade e uma paleta de cores que transmita calma e confiança.
3.  **Lógica Cliente:** Desenvolvimento da interatividade em `script.js`, garantindo que ações sejam tratadas de forma eficiente.
4.  **Lógica Servidor:** Implementação da camada de dados e negócios no arquivo `Allin.php`, que atua como o ponto de entrada principal para a comunicação entre o frontend e o banco de dados.

## Como Executar o Projeto

Para executar o MindCare localmente, você precisará de um ambiente que suporte PHP e MySQL (ex: XAMPP, WAMP, MAMP).

1.  **Clone o Repositório:**
    ```bash
    git clone [https://github.com/SeuUsuario/MindCare.git](https://github.com/SeuUsuario/MindCare.git)
    cd MindCare
    ```
2.  **Configuração:** Coloque os arquivos em um diretório acessível pelo seu servidor web (ex: `htdocs` ou `www`).
3.  **Banco de Dados:** Crie o banco de dados e as tabelas, utilizando o script SQL DDL que foi gerado previamente.
4.  **Acessar:** Abra seu navegador e navegue até a pasta do projeto no seu servidor local (ex: `http://localhost/MindCare/index.html`).
