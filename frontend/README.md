# Corporate Travel Manager - Frontend

## Estrutura

```bash
src/
├── assets/          # CSS global com Tailwind
├── components/      # Componentes reutilizáveis
├── composables/     # Composables Vue (hooks)
├── layouts/         # Layouts da aplicação
├── pages/           # Páginas/Views
├── router/          # Configuração de rotas
├── services/        # Serviços de API
├── stores/          # Pinia stores (state management)
└── utils/           # Utilitários e helpers
```

## Tecnologias

- **Vue 3** (Composition API com `<script setup>`)
- **Vue Router 4** (navegação com lazy loading)
- **Pinia** (State Management)
- **Axios** (HTTP Client com interceptors)
- **Tailwind CSS** (estilização utilitária)
- **Vite** (Build Tool e Dev Server)

## Classes Tailwind Customizadas

O projeto utiliza componentes customizados no `@layer components`:

- `.btn-primary` - Botão primário
- `.btn-secondary` - Botão secundário
- `.btn-danger` - Botão de ação perigosa
- `.input-field` - Input de formulário
- `.card` - Container card
