# Shared Module
## 📋 Overview
Provides common Value Objects used across multiple modules, primarily the `LawVector` (17D physics/culture blueprint) and `WorldStateVector` (runtime macro-state).

## 🏗️ Architecture
- **Domain Layer**: Pure PHP Value Objects, universally immutable.

## 📐 Structure
- `ValueObjects/` - Data structures shared system-wide.

## 🚀 Usage
```php
$law = LawVector::defaults();
$state = WorldStateVector::defaults();
```
