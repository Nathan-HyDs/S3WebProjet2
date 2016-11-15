DROP TABLE  IF EXISTS paniers,commandes, produits, users, typeProduits, etats;

-- --------------------------------------------------------
-- Structure de la table typeproduits
--
CREATE TABLE typeProduits (
  id int(10) NOT NULL,
  libelle varchar(50) DEFAULT NULL,
  PRIMARY KEY (id)
)  DEFAULT CHARSET=utf8;
-- Contenu de la table typeproduits
INSERT INTO typeProduits (id, libelle) VALUES
(1, 'MMORPG'),
(2, 'STR'),
(3, 'FPS'),
(4, "Hack'N'slash");
-- --------------------------------------------------------
-- Structure de la table etats

CREATE TABLE etats (
  id int(11) NOT NULL AUTO_INCREMENT,
  libelle varchar(20) NOT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 ;
-- Contenu de la table etats
INSERT INTO etats (id, libelle) VALUES
(1, 'A pr�parer'),
(2, 'Exp�di�');

-- --------------------------------------------------------
-- Structure de la table produits

CREATE TABLE produits (
  id int(10) NOT NULL AUTO_INCREMENT,
  typeProduit_id int(10) DEFAULT NULL,
  nom varchar(200) DEFAULT NULL,
  prix float(6,2) DEFAULT NULL,
  photo varchar(50) DEFAULT NULL,
  dispo tinyint(4) NOT NULL,
  stock int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_produits_typeProduits FOREIGN KEY (typeProduit_id) REFERENCES typeProduits (id)
) DEFAULT CHARSET=utf8 ;

INSERT INTO produits (id,typeProduit_id,nom,prix,photo,dispo,stock) VALUES
(NULL,2, 'Starcraft II : Wings of Liberty','19.99','sc2wol.jpg',1,25),
(NULL,2, 'Starcraft II : Hearth of the Swarm','19.99','sc2hots.jpg',1,30),
(NULL,2, 'Starcraft II : Legacy of the Void','39.99','sc2lotv.jpg',1,20),
(NULL,1, 'World of Warcraft : Gamecard 30 jours','12.99','wowgc2.jpg',1,44),
(NULL,1, 'World of Warcraft : Gamecard 60 jours','25.98','wowgc1.jpg',1,44),
(NULL,1, 'World of Warcraft : Legion','49.99','wowlegion.jpg',1,15),
(NULL,1, 'World of Warcaft : Battlechest','14.99','wowbc.jpg',1,4),
(NULL,3, 'Overwatch','39.99','ow.jpg',1,10),
(NULL,4, 'Diablo 3','19.99','d3.jpg',1,74),
(NULL,4, 'Diablo 3 : Reaper of Soul','19.99','d3rop.jpg',1,4),
(NULL,2, 'Warcraft 3','9.99','w3.jpg',1,10),
(NULL,2,'Warcraft 3 : Frozen Throne','9.99','w3ft.jpg',1,10);




-- --------------------------------------------------------
-- Structure de la table user
-- valide permet de rendre actif le compte (exemple controle par email )

CREATE TABLE users (
  id int(11) NOT NULL AUTO_INCREMENT,
  email varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  login varchar(255) NOT NULL,
  nom varchar(255) NOT NULL,
  code_postal varchar(255) NOT NULL,
  ville varchar(255) NOT NULL,
  adresse varchar(255) NOT NULL,
  valide tinyint NOT NULL,
  droit varchar(255) NOT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

-- Contenu de la table users
INSERT INTO users (id,login,password,email,valide,droit) VALUES
(1, 'admin', 'admin', 'admin@gmail.com',1,'DROITadmin'),
(2, 'vendeur', 'vendeur', 'vendeur@gmail.com',1,'DROITadmin'),
(3, 'client', 'client', 'client@gmail.com',1,'DROITclient'),
(4, 'client2', 'client2', 'client2@gmail.com',1,'DROITclient'),
(5, 'client3', 'client3', 'client3@gmail.com',1,'DROITclient');



-- --------------------------------------------------------
-- Structure de la table commandes
CREATE TABLE commandes (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  prix float(6,2) NOT NULL,
  date_achat  timestamp default CURRENT_TIMESTAMP,
  etat_id int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_commandes_users FOREIGN KEY (user_id) REFERENCES users (id),
  CONSTRAINT fk_commandes_etats FOREIGN KEY (etat_id) REFERENCES etats (id)
) DEFAULT CHARSET=utf8 ;



-- --------------------------------------------------------
-- Structure de la table paniers
CREATE TABLE paniers (
  id int(11) NOT NULL AUTO_INCREMENT,
  quantite int(11) NOT NULL,
  prix float(6,2) NOT NULL,
  dateAjoutPanier timestamp default CURRENT_TIMESTAMP,
  user_id int(11) NOT NULL,
  produit_id int(11) NOT NULL,
  commande_id int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_paniers_users FOREIGN KEY (user_id) REFERENCES users (id),
  CONSTRAINT fk_paniers_produits FOREIGN KEY (produit_id) REFERENCES produits (id),
  CONSTRAINT fk_paniers_commandes FOREIGN KEY (commande_id) REFERENCES commandes (id)
) DEFAULT CHARSET=utf8 ;

