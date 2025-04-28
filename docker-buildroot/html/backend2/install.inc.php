<?php
function installdb() {
  global $mysqli, $login;

  $mysqli->query('CREATE DATABASE '.BDDBASE.' CHARACTER SET=utf8');
  $mysqli->select_db(BDDBASE);
  $query=<<<END
CREATE TABLE IF NOT EXISTS users (
  email varchar(320) PRIMARY KEY,
  pwd varchar(255) NOT NULL,
  prof bit(1) NOT NULL,
  name varchar(300) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8
END;
  $mysqli->query($query);
  $query=<<<END
CREATE TABLE IF NOT EXISTS departments (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  title varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8
END;
  $mysqli->query($query);
  $query=<<<END
CREATE TABLE IF NOT EXISTS versions (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  title varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8
END;
  $mysqli->query($query);
  $query=<<<END
CREATE TABLE IF NOT EXISTS defconfs (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  defconfig varchar(120) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8
END;
  $mysqli->query($query);
  $query=<<<END
CREATE TABLE IF NOT EXISTS prop (
  idversion int(11) NOT NULL,
  iddefconf int(11) NOT NULL,
  idtoolchain int(11) NULL,
  PRIMARY KEY (idversion, iddefconf),
  FOREIGN KEY (idversion) REFERENCES versions (id) ON UPDATE RESTRICT ON DELETE CASCADE,
  FOREIGN KEY (iddefconf) REFERENCES defconfs (id) ON UPDATE RESTRICT ON DELETE CASCADE,
  FOREIGN KEY (idtoolchain) REFERENCES toolchains (id) ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8
END;
  $mysqli->query($query);
  $query=<<<END
CREATE TABLE IF NOT EXISTS toolchains (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  gcc varchar(8) NULL,
  headers varchar(16) NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8
END;
  $mysqli->query($query);
  $query=<<<END
CREATE TABLE IF NOT EXISTS speedups (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  title varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8
END;
  $mysqli->query($query);
  $query=<<<END
INSERT INTO speedups(id,title) VALUES
  (1,'Empty'),
  (2,'GrovePi in Python')
END;
  $mysqli->query($query);
  $query=<<<END
CREATE TABLE IF NOT EXISTS host_pkgs (
  id int(11),
  name varchar(200) NOT NULL,
  pri int(11) NOT NULL,
  PRIMARY KEY (id, name),
  FOREIGN KEY (id) REFERENCES speedups (id) ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8
END;
  $mysqli->query($query);
  $query=<<<END
INSERT INTO host_pkgs(id,name,pri) VALUES
  (2,'cmake',1),
  (2,'fakeroot',2),
  (2,'makedevs',3),
  (2,'e2fsprogs',4),
  (2,'python-cffi',5),
  (2,'python-pythran',6),
  (2,'flex',7),
  (2,'openssl',8),
  (2,'swig',9),
  (2,'dosfstools',10),
  (2,'genimage',11),
  (2,'mtools',12),
  (2,'patchelf',13),
  (2,'skeleton',14),
  (2,'pkgconf',15),
  (2,'zlib',16)
END;
  $mysqli->query($query);
  $query=<<<END
CREATE TABLE IF NOT EXISTS pkgs (
  id int(11),
  name varchar(200) NOT NULL,
  env varchar(200) NOT NULL,
  pri int(11) NOT NULL,
  PRIMARY KEY (id, name),
  FOREIGN KEY (id) REFERENCES speedups (id) ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8
END;
  $mysqli->query($query);
  $query=<<<END
INSERT INTO pkgs(id,name,env,pri) VALUES
  (2,'lapack','PACKAGE_LAPACK',1),
  (2,'openblas','PACKAGE_OPENBLAS',2),
  (2,'python-scipy','PACKAGE_PYTHON_SCIPY',3)
END;
  $mysqli->query($query);
  $query=<<<END
CREATE TABLE IF NOT EXISTS images (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  version int(11) NOT NULL,
  toolchain int(11) NOT NULL,
  speedup int(11) NOT NULL,
  install tinyint(1) NOT NULL,
  FOREIGN KEY (version) REFERENCES versions (id) ON UPDATE RESTRICT ON DELETE CASCADE,
  FOREIGN KEY (toolchain) REFERENCES toolchains (id) ON UPDATE RESTRICT ON DELETE CASCADE,
  FOREIGN KEY (speedup) REFERENCES speedups (id) ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8
END;
  $mysqli->query($query);
  $query=<<<END
CREATE TABLE IF NOT EXISTS projects (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  title varchar(200) NOT NULL,
  pub bit(1) NOT NULL,
  power tinyint(4) NULL,
  iddep int(11) NOT NULL,
  image int(11) NULL,
  FOREIGN KEY (iddep) REFERENCES departments (id) ON UPDATE RESTRICT ON DELETE CASCADE,
  FOREIGN KEY (image) REFERENCES images (id) ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8
END;
  $mysqli->query($query);
  $query=<<<END
CREATE TABLE IF NOT EXISTS act (
  id int(11) NOT NULL,
  email varchar(320) NOT NULL,
  token char(32) NULL,
  PRIMARY KEY (id, email),
  FOREIGN KEY (email) REFERENCES users (email) ON UPDATE RESTRICT ON DELETE CASCADE,
  FOREIGN KEY (id) REFERENCES projects (id) ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8
END;
  $mysqli->query($query);
  $query=<<<END
CREATE TABLE IF NOT EXISTS graph (
  id int(11) NOT NULL,
  timestamp bigint(20) NOT NULL,
  cpu smallint(4) NULL,
  mem smallint(4) NULL,
  swap smallint(4) NULL,
  lcpu int(11) NULL,
  lmem smallint(4) NULL,
  PRIMARY KEY (id, timestamp)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
END;
  $mysqli->query($query);
  $pwd=crypt($_POST['pwd'], '$1$'.substr(base64_encode(random_bytes(6)),0,6).'$');
  $mysqli->query("INSERT INTO users VALUES ('$login', '$pwd', 1,'FIRST ADMIN')");
  $_SESSION['prof']=1;
  $_SESSION['name']='FIRST ADMIN';
  $query=<<<END
  CREATE USER 'buildroot'@'%' IDENTIFIED BY 'buildroot';
  GRANT SELECT ON *.* TO 'buildroot'@'%' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
  CREATE USER 'mqtt'@'172.31.255.6' IDENTIFIED BY 'mqtt';
  GRANT SELECT,INSERT,UPDATE,DELETE ON *.* TO 'mqtt'@'172.31.255.6' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
  FLUSH PRIVILEGES;
END;
  $mysqli->multi_query($query);
}
?>