# Quick Reference - Padding Client

## ⚡ TL;DR

Le padding est **automatique** dans le layout. Ne l'ajoutez PAS dans vos vues !

---

## 📋 Templates

### Vue Simple
```blade
@extends('layouts.client')
@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Contenu -->
</div>
@endsection
```

### Vue avec Fond Pleine Largeur
```blade
@extends('layouts.client')
@section('content')
<div class="bg-gradient-to-br from-purple-50 via-white to-indigo-50 -mx-4 -my-4 lg:-mx-6 lg:-my-6 px-4 py-4 lg:px-6 lg:py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Contenu -->
    </div>
</div>
@endsection
```

---

## ✅ À Faire

- ✅ Utiliser `max-w-7xl mx-auto`
- ✅ Laisser le layout gérer le padding
- ✅ Utiliser `-mx-4 -my-4` pour fond pleine largeur

## ❌ À Ne Pas Faire

- ❌ `container mx-auto px-4 py-6`
- ❌ Ajouter du padding au conteneur principal
- ❌ Utiliser `container` (préférer `max-w-7xl`)

---

## 📐 Padding Automatique

- **Mobile**: 16px (px-4 py-4)
- **Desktop**: 24px (lg:px-6 lg:py-6)

---

## 🔗 Docs Complètes

- `CORRECTION_PADDING_LAYOUT_CLIENT.md` - Documentation complète
- `GUIDE_DEVELOPPEUR_PADDING.md` - Guide développeur
- `TEST_PADDING_CLIENT.md` - Guide de test
