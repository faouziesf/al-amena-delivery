-- Script de nettoyage complet des wallets
-- Exécuter avec: sqlite3 database/database.sqlite < cleanup_wallets.sql

-- 1. Afficher les statistiques avant nettoyage
.print "=== AVANT NETTOYAGE ==="
.print "Nombre total de wallets:"
SELECT COUNT(*) FROM user_wallets;

.print "Wallets en double:"
SELECT user_id, COUNT(*) as count 
FROM user_wallets 
GROUP BY user_id 
HAVING COUNT(*) > 1;

-- 2. Supprimer les wallets en double (garder le plus ancien)
.print "=== SUPPRESSION DES DOUBLONS ==="
DELETE FROM user_wallets 
WHERE id NOT IN (
    SELECT MIN(id) 
    FROM user_wallets 
    GROUP BY user_id
);

-- 3. Supprimer les wallets orphelins
.print "=== SUPPRESSION DES WALLETS ORPHELINS ==="
DELETE FROM user_wallets 
WHERE user_id NOT IN (SELECT id FROM users);

-- 4. Créer les wallets manquants pour CLIENT et DELIVERER
.print "=== CRÉATION DES WALLETS MANQUANTS ==="
INSERT INTO user_wallets (user_id, balance, pending_amount, frozen_amount, created_at, updated_at)
SELECT 
    u.id,
    0.000,
    0.000,
    0.000,
    datetime('now'),
    datetime('now')
FROM users u
WHERE u.role IN ('CLIENT', 'DELIVERER')
AND u.id NOT IN (SELECT user_id FROM user_wallets);

-- 5. Afficher les statistiques après nettoyage
.print "=== APRÈS NETTOYAGE ==="
.print "Nombre total de wallets:"
SELECT COUNT(*) FROM user_wallets;

.print "Clients avec wallet:"
SELECT COUNT(*) FROM users u 
INNER JOIN user_wallets w ON u.id = w.user_id 
WHERE u.role = 'CLIENT';

.print "Livreurs avec wallet:"
SELECT COUNT(*) FROM users u 
INNER JOIN user_wallets w ON u.id = w.user_id 
WHERE u.role = 'DELIVERER';

.print "Vérification doublons (doit être vide):"
SELECT user_id, COUNT(*) as count 
FROM user_wallets 
GROUP BY user_id 
HAVING COUNT(*) > 1;

.print "=== NETTOYAGE TERMINÉ ==="