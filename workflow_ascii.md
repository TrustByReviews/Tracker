# 🔄 Diagrama de Flujo del Workflow de Tareas (ASCII)

## 📊 Flujo Principal

```
🚀 DESARROLLADOR
    |
    v
🟢 IN PROGRESS (Trabajando)
    |
    v
✅ DONE (Completada)
    |
    v
🟡 READY FOR TEST (Lista para QA)
    |
    v
🟡 TESTING (QA testeando)
    |
    v
{QA DECIDE}
    |
    +-- ✅ APPROVED (QA aprueba)
    |       |
    |       v
    |   🟡 TEAM LEADER REVIEW
    |       |
    |       v
    |   {TL DECIDE}
    |       |
    |       +-- ✅ FINAL APPROVED (TL aprueba)
    |       |       |
    |       |       v
    |       |   🎉 TAREA TERMINADA
    |       |
    |       +-- 🔄 CHANGES REQUESTED (TL pide cambios)
    |               |
    |               v
    |           🔄 DEV CORRIGE
    |               |
    |               v
    |           ✅ DEV COMPLETA
    |               |
    |               v
    |           🟡 READY FOR TEST (Re-testing QA)
    |
    +-- ❌ REJECTED (QA rechaza)
            |
            v
        🔄 DEV CORRIGE
            |
            v
        ✅ DEV COMPLETA
            |
            v
        🟡 READY FOR TEST (Re-testing QA)
```

## 🔄 Ciclos de Re-trabajo

### **Ciclo 1: QA Rechaza**
```
QA Rechaza → Dev Corrige → Dev Completa → READY FOR TEST → QA Testing → ...
```

### **Ciclo 2: TL Pide Cambios**
```
TL Pide Cambios → Dev Implementa → Dev Completa → READY FOR TEST → QA Re-testing → ...
```

## 📋 Estados y Transiciones

| Estado | Descripción | Siguiente Estado Posible |
|--------|-------------|-------------------------|
| `IN PROGRESS` | Dev trabajando | `DONE` |
| `DONE` | Dev completó | `READY FOR TEST` |
| `READY FOR TEST` | Lista para QA | `TESTING` |
| `TESTING` | QA testeando | `APPROVED` / `REJECTED` |
| `APPROVED` | QA aprobó | `TEAM LEADER REVIEW` |
| `REJECTED` | QA rechazó | `READY FOR TEST` (después de corrección) |
| `TEAM LEADER REVIEW` | TL revisando | `FINAL APPROVED` / `CHANGES REQUESTED` |
| `CHANGES REQUESTED` | TL pidió cambios | `READY FOR TEST` (después de corrección) |
| `FINAL APPROVED` | TL aprobó finalmente | `TAREA TERMINADA` |

## 🎯 Reglas Clave

1. **QA siempre testea primero** después del desarrollo
2. **TL solo revisa tareas aprobadas por QA**
3. **Si TL pide cambios, QA debe re-testear**
4. **Cada corrección vuelve al inicio del flujo QA**
5. **Solo TL puede dar aprobación final**

## 🔧 Implementación Técnica

### **QA Status Flow**
```
pending → ready_for_test → testing → approved/rejected
```

### **Team Leader Flow**
```
null → requested_changes → final_approval
```

### **Transiciones Automáticas**
- **Dev completa** → `qa_status = 'ready_for_test'`
- **QA aprueba** → `qa_status = 'approved'`
- **QA rechaza** → `qa_status = 'rejected'`
- **TL pide cambios** → `team_leader_requested_changes = true`
- **Dev completa cambios** → `qa_status = 'ready_for_test'` (re-testing)
- **TL aprueba finalmente** → `team_leader_final_approval = true`

## 📧 Notificaciones Automáticas

- 📧 QA notificado cuando tarea lista para testing
- 📧 Dev notificado cuando QA rechaza
- 📧 Dev notificado cuando TL pide cambios
- 📧 QA notificado cuando dev completa cambios
- 📧 TL notificado cuando QA aprueba
- 📧 Proyecto notificado cuando TL aprueba finalmente
