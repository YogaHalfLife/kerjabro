# Host: localhost  (Version 8.0.30)
# Date: 2025-11-20 20:48:44
# Generator: MySQL-Front 6.0  (Build 2.20)


#
# Structure for table "failed_jobs"
#

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "failed_jobs"
#


#
# Structure for table "master_divisi"
#

CREATE TABLE `master_divisi` (
  `id_divisi` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_divisi` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isactive` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_divisi`),
  UNIQUE KEY `master_divisi_nama_divisi_unique` (`nama_divisi`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "master_divisi"
#

INSERT INTO `master_divisi` VALUES (1,'Web Programmer',1,'2025-10-02 03:37:51','2025-10-02 03:37:51',NULL),(2,'test',0,'2025-10-02 03:38:54','2025-10-02 03:42:04','2025-10-02 03:42:04'),(3,'Mobile Programmer',1,'2025-10-02 03:42:53','2025-10-02 03:42:53',NULL),(4,'System Analist',1,'2025-10-02 03:45:53','2025-10-02 03:45:53',NULL),(5,'System Support',1,'2025-10-02 03:46:25','2025-10-02 03:46:25',NULL),(6,'Network Engineer',1,'2025-10-02 03:47:15','2025-10-02 03:47:15',NULL),(7,'All',1,'2025-10-02 03:47:33','2025-10-02 03:47:33',NULL);

#
# Structure for table "master_pegawai"
#

CREATE TABLE `master_pegawai` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_pegawai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_pegawai` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_divisi` bigint unsigned NOT NULL,
  `isactive` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `master_pegawai_kode_pegawai_unique` (`kode_pegawai`),
  KEY `master_pegawai_id_divisi_foreign` (`id_divisi`),
  CONSTRAINT `master_pegawai_id_divisi_foreign` FOREIGN KEY (`id_divisi`) REFERENCES `master_divisi` (`id_divisi`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "master_pegawai"
#

INSERT INTO `master_pegawai` VALUES (1,'10012','Yoga Febrian Maulana',1,1,'2025-10-02 04:11:16','2025-10-02 04:11:35',NULL),(2,'10006','Linggar Pangestu',4,1,'2025-10-02 04:12:28','2025-10-02 04:12:28',NULL),(3,'10003','Afandy Tumpal Halomoan',4,1,'2025-10-02 06:41:55','2025-10-02 06:41:55',NULL),(4,'10004','Indra Armaulana',1,1,'2025-10-02 06:42:19','2025-10-02 06:42:19',NULL),(5,'10005','Indra Kurniawan',1,1,'2025-10-02 06:42:40','2025-10-02 06:42:40',NULL),(6,'10007','Nindy Permatasari',5,1,'2025-10-02 06:43:03','2025-10-02 06:43:03',NULL),(7,'10008','Sandi Saputra',5,1,'2025-10-02 06:43:25','2025-10-02 06:43:25',NULL),(8,'10009','Saharudin',3,1,'2025-10-02 06:43:45','2025-10-02 06:43:45',NULL),(9,'10011','Tommy Wijaya Putra',5,1,'2025-10-02 06:44:16','2025-10-02 06:44:16',NULL),(10,'10101001','test',7,1,'2025-10-02 06:45:28','2025-10-02 06:45:41','2025-10-02 06:45:41'),(11,'PGW001','All Team',7,1,'2025-10-06 03:57:40','2025-10-07 02:12:01','2025-10-07 02:12:01'),(12,'10010','Suharyadi',1,1,'2025-10-07 02:12:54','2025-10-07 02:12:54',NULL),(13,'10013','Hafidz Rachmat Riandi',1,1,'2025-10-07 02:14:57','2025-10-07 02:14:57',NULL),(14,'10000','All Team',7,1,'2025-10-07 02:17:03','2025-10-07 02:17:03',NULL);

#
# Structure for table "migrations"
#

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "migrations"
#

INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2025_10_02_021306_create_master_divisi_table',2),(6,'2025_10_02_040504_create_master_pegawai_table',3),(7,'2025_10_02_074407_create_trans_pekerjaan_table',4),(8,'2025_10_02_074419_create_trans_pekerjaan_foto_table',4),(9,'2025_10_07_005637_add_judul_to_trans_pekerjaan_table',5),(10,'2025_11_18_080146_create_trans_pekerjaan_pegawai_table',6),(11,'2025_11_19_025755_add_id_divisi_to_trans_pekerjaan_pegawai_table',7);

#
# Structure for table "password_reset_tokens"
#

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "password_reset_tokens"
#


#
# Structure for table "personal_access_tokens"
#

CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "personal_access_tokens"
#


#
# Structure for table "trans_pekerjaan"
#

CREATE TABLE `trans_pekerjaan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `judul_pekerjaan` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail_pekerjaan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `pegawai_id` bigint unsigned DEFAULT NULL,
  `id_divisi` bigint unsigned NOT NULL,
  `foto_sebelum` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_sesudah` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bulan` char(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trans_pekerjaan_pegawai_id_foreign` (`pegawai_id`),
  KEY `trans_pekerjaan_id_divisi_foreign` (`id_divisi`),
  KEY `trans_pekerjaan_bulan_pegawai_id_id_divisi_index` (`bulan`,`pegawai_id`,`id_divisi`),
  KEY `trans_pekerjaan_judul_pekerjaan_index` (`judul_pekerjaan`),
  CONSTRAINT `trans_pekerjaan_id_divisi_foreign` FOREIGN KEY (`id_divisi`) REFERENCES `master_divisi` (`id_divisi`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `trans_pekerjaan_pegawai_id_foreign` FOREIGN KEY (`pegawai_id`) REFERENCES `master_pegawai` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "trans_pekerjaan"
#

INSERT INTO `trans_pekerjaan` VALUES (1,'','test',1,1,NULL,NULL,'2025-10','2025-10-02 01:20:13','2025-10-02 01:22:58','2025-10-02 01:22:58'),(2,'','test',1,1,NULL,NULL,'2025-10','2025-10-02 01:22:32','2025-10-02 01:22:53','2025-10-02 01:22:53'),(3,'','test',6,1,NULL,NULL,'2025-10','2025-10-02 01:23:17','2025-10-02 01:33:18','2025-10-02 01:33:18'),(4,'','Test upload kerjaan',1,1,NULL,NULL,'2025-10','2025-10-02 18:30:19','2025-10-02 18:52:19','2025-10-02 18:52:19'),(5,'','test multiple upload',1,1,NULL,NULL,'2025-10','2025-10-02 18:31:05','2025-10-02 18:52:12','2025-10-02 18:52:12'),(6,'','test upload',1,1,NULL,NULL,'2025-10','2025-10-02 18:58:03','2025-10-02 18:59:48','2025-10-02 18:59:48'),(7,'','test',1,1,NULL,NULL,'2025-10','2025-10-02 19:03:58','2025-10-02 19:04:48','2025-10-02 19:04:48'),(8,'','test',1,1,NULL,NULL,'2025-10','2025-10-02 19:04:30','2025-10-02 19:04:44','2025-10-02 19:04:44'),(9,'','test',1,1,NULL,NULL,'2025-10','2025-10-02 19:05:09','2025-10-02 19:11:53','2025-10-02 19:11:53'),(10,'','asdasd',1,1,NULL,NULL,'2025-10','2025-10-02 19:06:02','2025-10-02 19:11:49','2025-10-02 19:11:49'),(11,'','asdasd',1,1,NULL,NULL,'2025-10','2025-10-02 19:06:24','2025-10-02 19:11:45','2025-10-02 19:11:45'),(12,'','asdasd',1,1,NULL,NULL,'2025-10','2025-10-02 19:08:06','2025-10-02 19:11:40','2025-10-02 19:11:40'),(13,'','asdasd',1,1,NULL,NULL,'2025-10','2025-10-02 19:08:29','2025-10-02 19:11:28','2025-10-02 19:11:28'),(14,'','test',1,1,NULL,NULL,'2025-10','2025-10-02 19:11:21','2025-10-02 19:15:31','2025-10-02 19:15:31'),(15,'','test upload',1,1,NULL,NULL,'2025-10','2025-10-02 19:16:02','2025-10-02 19:16:20','2025-10-02 19:16:20'),(16,'','test uploads',1,1,NULL,NULL,'2025-10','2025-10-02 19:26:03','2025-10-02 19:50:40','2025-10-02 19:50:40'),(17,'','test lagi',1,1,NULL,NULL,'2025-10','2025-10-02 19:38:48','2025-10-02 19:50:34','2025-10-02 19:50:34'),(18,'','test',1,1,NULL,NULL,'2025-10','2025-10-02 19:51:02','2025-10-02 19:59:26','2025-10-02 19:59:26'),(19,'','Test upload',1,1,NULL,NULL,'2025-10','2025-10-02 20:11:24','2025-10-05 20:55:28','2025-10-05 20:55:28'),(20,'','Percobaan',6,5,NULL,NULL,'2025-10','2025-10-02 20:12:41','2025-10-05 20:55:24','2025-10-05 20:55:24'),(21,'','test 2 laporan',1,1,NULL,NULL,'2025-10','2025-10-03 00:06:56','2025-10-05 20:55:20','2025-10-05 20:55:20'),(22,'','sadsd',8,3,NULL,NULL,'2025-09','2025-10-03 01:25:23','2025-10-05 20:55:17','2025-10-05 20:55:17'),(23,'','sdgsdfgfgs',1,1,NULL,NULL,'2025-10','2025-10-05 20:36:17','2025-10-05 20:55:05','2025-10-05 20:55:05'),(24,'','test',11,7,NULL,NULL,'2025-10','2025-10-05 20:58:03','2025-10-05 20:58:28','2025-10-05 20:58:28'),(25,'Test','test judul kerjabro',1,1,NULL,NULL,'2025-10','2025-10-06 18:31:55','2025-10-06 19:08:20','2025-10-06 19:08:20'),(26,'Test dropzone','memperbaiki dropzone agar tidak auto explorer',1,1,NULL,NULL,'2025-10','2025-10-06 18:55:58','2025-10-06 19:00:19','2025-10-06 19:00:19'),(27,'Test Dropzone','perbarui dropzone agar tidak auto explorer',1,1,NULL,NULL,'2025-10','2025-10-06 19:02:00','2025-10-06 19:08:16','2025-10-06 19:08:16'),(28,'qwerty','popopopopopopopopopo',1,1,NULL,NULL,'2025-10','2025-10-06 19:07:35','2025-10-06 19:08:12','2025-10-06 19:08:12'),(29,'Merancang aplikasi kerjabro','membuat aplikasi kerjabro untuk pengelolaan data pekerjaan instalasi IT RSUD Raja Ahmad Tabib',1,1,NULL,NULL,'2025-10-07','2025-10-06 19:20:28','2025-10-06 20:19:31','2025-10-06 20:19:31'),(30,'Testing aplikasi kerjabro','melakukan testing terhadap aplikasi kerjabro yang baru dilaunching oleh web programmer.',6,5,NULL,NULL,'2025-10','2025-10-06 19:23:42','2025-10-06 20:19:27','2025-10-06 20:19:27'),(31,'Finalisasi RUMSING Dashboard','Finalisasi aplikasi Rumah Singgah Dashboard untuk user administrator.',8,3,NULL,NULL,'2025-10-07','2025-10-06 21:37:04','2025-11-19 01:15:59','2025-11-19 01:15:59'),(32,'Kerjaan pak sahar termaksud kerjaan awak','Kerjaan pak sahar termaksud kerjaan awak',12,1,NULL,NULL,'2025-10','2025-10-06 23:18:59','2025-10-06 23:24:58','2025-10-06 23:24:58'),(33,'Form Pengkajian Awal dan Ulang Pasien Terminal dan Keluarganya','Telah dilakukan pengkajian awal pasien terminal dan keluarganya, yang saat ini baru mencapai tahap pengisian form bagian “Informasi Pasien” dan “Gejala serta Kesulitan Bernapas”. Pada tahap ini dilakukan pendataan identitas pasien, riwayat penyakit, serta observasi awal terhadap kondisi pernapasan pasien untuk menilai adanya sesak atau gangguan bernapas. Data yang diperoleh akan menjadi dasar untuk melanjutkan pengkajian pada bagian berikutnya.',12,1,NULL,NULL,'2025-10-07','2025-10-06 23:30:13','2025-10-24 01:42:16','2025-10-24 01:42:16'),(34,'Bugs fixing kepuasan pasien notifikasi log','Dilakukan perbaikan (bugs fixing) pada modul Kepuasan Pasien, khususnya pada proses penyimpanan referensi ID yang digunakan untuk pengelolaan notifikasi di aplikasi Android.\r\nPerbaikan ini bertujuan agar setiap notifikasi yang dikirim ke perangkat pengguna memiliki referensi ID yang tersimpan dengan benar, sehingga sistem dapat melakukan pengelompokan, pelacakan, dan penghapusan data notifikasi dengan lebih mudah dan terstruktur.\r\nHasil dari perbaikan ini memastikan sinkronisasi data antara server dan aplikasi berjalan stabil, serta meningkatkan akurasi dalam penampilan notifikasi kepuasan pasien di sisi pengguna.',12,1,NULL,NULL,'2025-10-07','2025-10-06 23:44:23','2025-11-19 01:15:54','2025-11-19 01:15:54'),(35,'test','asdasdasdasd',1,1,NULL,NULL,'2025-10-07','2025-10-07 00:07:43','2025-10-07 00:08:18','2025-10-07 00:08:18'),(36,'xczxc','zxczxczxc',1,1,NULL,NULL,'2025-10-07','2025-10-07 00:24:02','2025-10-07 00:37:51','2025-10-07 00:37:51'),(37,'fkajsfklja','kaslfkalskf',6,5,NULL,NULL,'2025-10-07','2025-10-07 00:31:56','2025-10-07 00:33:36','2025-10-07 00:33:36'),(38,'sfalskfl','kfl;asmf;lmas',6,5,NULL,NULL,'2025-10-07','2025-10-07 00:38:02','2025-10-07 00:43:45','2025-10-07 00:43:45'),(39,'asdasd','asdasdasd',7,5,NULL,NULL,'2025-10-07','2025-10-07 00:40:05','2025-10-07 00:40:15','2025-10-07 00:40:15'),(40,'tytyd','sunat',7,5,NULL,NULL,'2025-10-07','2025-10-07 00:41:33','2025-10-07 00:43:39','2025-10-07 00:43:39'),(41,'aFNfn','KALSMFKSAMFSA',6,5,NULL,NULL,'2025-10-07','2025-10-07 00:42:26','2025-10-07 00:43:34','2025-10-07 00:43:34'),(42,'Sebagai System Support RSUD RAT','Sebagai System Support di RSUD RAT, saya bertanggung jawab memastikan kelancaran sistem informasi rumah sakit dengan berbagai tugas teknis dan administratif. Pekerjaan meliputi membuka data pasien yang telah di-close untuk keperluan koreksi atau pembaruan, menangani proses kondisi akhir serta registrasi keluar pasien agar data rekam medis dan billing berjalan dengan benar. Selain itu, saya melakukan pemeliharaan serta perbaikan perangkat keras dan jaringan, termasuk troubleshooting printer label di berbagai unit pelayanan seperti laboratorium, farmasi, dan ruang rawat inap. Saya juga berperan dalam mengatasi gangguan jaringan yang dapat menghambat akses aplikasi rumah sakit, memastikan seluruh sistem kembali berfungsi optimal. Di bidang administrasi keuangan, saya turut serta dalam proses perhitungan jasa pelayanan sebagai bagian dari tim remunerasi, membantu memastikan ketepatan pembagian jasa sesuai kontribusi masing-masing tenaga kesehatan. Dengan koordinasi yang baik antarunit dan respons cepat terhadap kendala teknis, saya berupaya mendukung kelancaran operasional, meningkatkan efisiensi kerja, serta menjaga kualitas layanan teknologi informasi di lingkungan RSUD RAT.',9,5,NULL,NULL,'2025-10-20','2025-10-07 03:06:18','2025-10-24 01:42:02','2025-10-24 01:42:02'),(43,'Perancangan awal sistem informasi jasa pelayanan','Dilaksanakannya perancangan Sistem Informasi Jasa Pelayanan mencakup pembangunan modul Master Unit (dengan detail unit/DET_MST_UNIT pada halaman detail), Master Kategori Klaim, Referensi Jenis Klaim, serta Mapping Kategori–Unit yang menghubungkan kategori klaim dengan unit; pengembangan Trans Kategori Klaim dan Trans Pengklaiman dengan penomoran otomatis No Voucher dan No Klaim berbasis tanggal; penyelarasan Dashboard untuk menampilkan statistik riil per modul dan daftar transaksi terbaru; serta implementasi Global Search di navbar dengan saran pencarian real-time, navigasi keyboard, dan highlight kata kunci, sehingga alur kerja dan navigasi data antarmodul menjadi lebih cepat, rapi, dan konsisten.',1,1,NULL,NULL,'2025-10-01','2025-10-07 18:18:00','2025-10-24 01:41:55','2025-10-24 01:41:55'),(44,'Update patch INACBG','Mengupdate aplikasi eklaim inacbg untuk pengklaiman',2,4,NULL,NULL,'2025-10-08','2025-10-07 20:21:33','2025-10-24 01:41:50','2025-10-24 01:41:50'),(45,'Membuat username login karyawan baru','Username adalah identitas unik yang digunakan oleh setiap pengguna untuk login ke Sistem SIMETRIS (Sistem Informasi Manajemen Terintegrasi Rumah Sakit). Melalui username ini, sistem bisa mengenali siapa yang mengakses, dari unit mana, dan dengan hak akses apa.',6,5,NULL,NULL,'2025-10-08','2025-10-08 00:10:23','2025-11-19 01:15:47','2025-11-19 01:15:47'),(46,'Membuat query pengambilan data raber','Membuat query pengambilan data raber untuk pembagian jasa',2,4,NULL,NULL,'2025-10-08','2025-10-08 01:32:38','2025-10-24 01:41:40','2025-10-24 01:41:40'),(47,'test','asdasddasdfasd',1,1,NULL,NULL,'2025-11-18','2025-11-18 07:53:11','2025-11-18 07:55:57','2025-11-18 07:55:57'),(48,'test','asdasddasdfasd',4,1,NULL,NULL,'2025-11-18','2025-11-18 07:53:11','2025-11-19 01:15:41','2025-11-19 01:15:41'),(49,'asdasd','asdasdasd',NULL,1,NULL,NULL,'2025-11-18','2025-11-18 08:21:36','2025-11-19 01:15:34','2025-11-19 01:15:34'),(50,'sdfdsfsdf','sdfsdfsdfsdfsdf',NULL,1,NULL,NULL,'2025-11-19','2025-11-19 01:34:33','2025-11-19 03:18:16','2025-11-19 03:18:16'),(51,'test','testttt',NULL,1,NULL,NULL,'2025-11-19','2025-11-19 01:50:56','2025-11-19 03:18:11','2025-11-19 03:18:11'),(52,'testingasdasdasdasd','djhkasdsdhjkshjkasjkhdkjaskhjkhjdsk',NULL,1,NULL,NULL,'2025-11-19','2025-11-19 03:12:26','2025-11-19 03:18:07','2025-11-19 03:18:07'),(53,'test multi pegawai','ini test multi pegawai',NULL,1,NULL,NULL,'2025-11-19','2025-11-19 03:19:43','2025-11-19 03:19:43',NULL),(54,'asdasd','asdasdas',NULL,7,NULL,NULL,'2025-11-19','2025-11-19 03:51:45','2025-11-19 03:52:20',NULL);

#
# Structure for table "trans_pekerjaan_foto"
#

CREATE TABLE `trans_pekerjaan_foto` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pekerjaan_id` bigint unsigned NOT NULL,
  `kategori` enum('sebelum','sesudah') COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caption` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trans_pekerjaan_foto_pekerjaan_id_kategori_sort_index` (`pekerjaan_id`,`kategori`,`sort`),
  KEY `trans_pekerjaan_foto_kategori_index` (`kategori`),
  CONSTRAINT `trans_pekerjaan_foto_pekerjaan_id_foreign` FOREIGN KEY (`pekerjaan_id`) REFERENCES `trans_pekerjaan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "trans_pekerjaan_foto"
#

INSERT INTO `trans_pekerjaan_foto` VALUES (1,1,'sebelum','trans_pekerjaan/tkhWGhTbpdaVS5UwoyYsgbphVB0war3vg0ySLSzq.png',NULL,0,'2025-10-02 01:20:14','2025-10-02 01:22:58','2025-10-02 01:22:58'),(2,1,'sesudah','trans_pekerjaan/WWn2Tyy6ynSwlvLssdSF1ABJJYgFTcd49R5JhrGw.png',NULL,0,'2025-10-02 01:20:14','2025-10-02 01:22:58','2025-10-02 01:22:58'),(3,2,'sebelum','trans_pekerjaan/AzVUZGJndPgCeSbdbmCOj1BoV7A8EJHrK2AcBANI.png',NULL,0,'2025-10-02 01:22:32','2025-10-02 01:22:53','2025-10-02 01:22:53'),(4,2,'sesudah','trans_pekerjaan/2QHFWs62V8q6lT5hklUha8hjcxReKjM0EJlDczwm.png',NULL,0,'2025-10-02 01:22:32','2025-10-02 01:22:53','2025-10-02 01:22:53'),(5,3,'sebelum','trans_pekerjaan/P9neDsgE8ETSh0RUabhPHt7NFvSnYKnpmJGSIiKQ.png',NULL,0,'2025-10-02 01:23:17','2025-10-02 01:33:18','2025-10-02 01:33:18'),(6,3,'sesudah','trans_pekerjaan/7hDP9z7xuoHc765QGbFbIc4AHU0idj9yrCv0BBgJ.png',NULL,0,'2025-10-02 01:23:17','2025-10-02 01:33:18','2025-10-02 01:33:18'),(7,4,'sebelum','trans_pekerjaan/wIx2VeXEK3h2vDgADY9iPDN7mqO81ezukemPDJfe.png',NULL,0,'2025-10-02 18:30:21','2025-10-02 18:52:19','2025-10-02 18:52:19'),(8,4,'sesudah','trans_pekerjaan/HeeZw2VDLwqfqLwcYu548cmNv4z2x4xoZ7P5BJ6F.png',NULL,0,'2025-10-02 18:30:21','2025-10-02 18:52:19','2025-10-02 18:52:19'),(9,5,'sesudah','trans_pekerjaan/hIfsYV9dBggildIKLzUflnxn2cmyhrbWbzv7GBh7.png',NULL,0,'2025-10-02 18:31:05','2025-10-02 18:52:12','2025-10-02 18:52:12'),(10,5,'sesudah','trans_pekerjaan/aqeC1TC1qpqOCQNRR9x2nZetOHuCGYHvsvBU302N.png',NULL,1,'2025-10-02 18:31:05','2025-10-02 18:52:12','2025-10-02 18:52:12'),(11,6,'sebelum','trans_pekerjaan/O4zUeNTTE0i21TWBK8YgE8nDrYspp9uuV5o8yh0T.png',NULL,0,'2025-10-02 18:58:04','2025-10-02 18:59:48','2025-10-02 18:59:48'),(12,6,'sesudah','trans_pekerjaan/2ExW09bMo5o5Ypkd5xUStyHDDY3OzmLcckfu5qQt.png',NULL,0,'2025-10-02 18:58:04','2025-10-02 18:59:48','2025-10-02 18:59:48'),(13,6,'sesudah','trans_pekerjaan/UED4sAoDEqHZIiUZrLT2yZYTM7A204mxseRNm7He.png',NULL,1,'2025-10-02 18:58:04','2025-10-02 18:59:32','2025-10-02 18:59:32'),(14,14,'sesudah','trans_pekerjaan/wa7m6jT5fM2oaM6xymvps7VMGBwUs81GFHoRc0f9.png',NULL,0,'2025-10-02 19:11:22','2025-10-02 19:15:31','2025-10-02 19:15:31'),(15,15,'sebelum','trans_pekerjaan/ivqjPiLe6st2sCbbRFVFc5nLfWxcP1NS3shh4N3k.png',NULL,0,'2025-10-02 19:16:02','2025-10-02 19:16:20','2025-10-02 19:16:20'),(16,15,'sebelum','trans_pekerjaan/cfKk0rwrDJX5LcXHQfE1HS7HSuX2JmuYM8Zoh28w.png',NULL,1,'2025-10-02 19:16:02','2025-10-02 19:16:14','2025-10-02 19:16:14'),(17,15,'sesudah','trans_pekerjaan/W6QnSTPzzxU0eJtTT2UbvEapRXLzxuN5tQjWXt8W.png',NULL,0,'2025-10-02 19:16:02','2025-10-02 19:16:20','2025-10-02 19:16:20'),(18,16,'sebelum','trans_pekerjaan/ojtDuINB3YXJHTNbc4fP0E0c5DEfJTA9OFygjQWw.png',NULL,0,'2025-10-02 19:26:03','2025-10-02 19:50:40','2025-10-02 19:50:40'),(19,16,'sebelum','trans_pekerjaan/YOLsqwjWt3ijjGKYaZF9HwoDmBDcTgwKXaS0WDbv.png',NULL,1,'2025-10-02 19:26:03','2025-10-02 19:26:13','2025-10-02 19:26:13'),(20,16,'sesudah','trans_pekerjaan/FCbFkOidUrpYXDCsOoKynRtGyhHUmpTULlY3a6c3.png',NULL,0,'2025-10-02 19:26:03','2025-10-02 19:50:40','2025-10-02 19:50:40'),(21,17,'sebelum','trans_pekerjaan/k7ybycqwfXuiBMy0INM7FwWdu0HzGvLLMJEvIY3t.jpg',NULL,0,'2025-10-02 19:38:48','2025-10-02 19:50:34','2025-10-02 19:50:34'),(22,17,'sesudah','trans_pekerjaan/yhMAAJ3JGXoPep1pMZspemo4g2qq7tuMlkZu1CXN.jpg',NULL,0,'2025-10-02 19:38:48','2025-10-02 19:50:34','2025-10-02 19:50:34'),(23,18,'sebelum','trans_pekerjaan/e7MQ9Fs3sF59ahwdeSM8ecplphthUjz9KohhlaBz.png',NULL,0,'2025-10-02 19:51:02','2025-10-02 19:59:26','2025-10-02 19:59:26'),(24,18,'sebelum','trans_pekerjaan/LuHOP0CmFMDrhNn0N2EiL2M36Dys5cvYmqPliSSj.png',NULL,1,'2025-10-02 19:51:02','2025-10-02 19:59:26','2025-10-02 19:59:26'),(25,18,'sesudah','trans_pekerjaan/JlCnQNxQwy4l0s7x6cYRPvpzmBdV9Kjaj8t1ULXx.jpg',NULL,0,'2025-10-02 19:51:02','2025-10-02 19:59:26','2025-10-02 19:59:26'),(26,18,'sesudah','trans_pekerjaan/f6bTt2LyQJ8HRon5QtviAYIPsghCjDkbsMAoOiUR.webp',NULL,1,'2025-10-02 19:51:02','2025-10-02 19:59:26','2025-10-02 19:59:26'),(27,19,'sebelum','trans_pekerjaan/cvtbNhIlso6Z1xNj3OdQZRcRWIcO0nmFqgdlDucL.png',NULL,0,'2025-10-02 20:11:24','2025-10-05 20:55:28','2025-10-05 20:55:28'),(28,19,'sesudah','trans_pekerjaan/edSDEPxnO6TUECHiDGz04CQ0mQOrrhXFOycfmEUe.jpg',NULL,0,'2025-10-02 20:11:24','2025-10-05 20:55:28','2025-10-05 20:55:28'),(29,19,'sesudah','trans_pekerjaan/IvPsmJ4ZY0j2MHBmdkVUoIb0DcjTaB8NBF7X72YH.webp',NULL,1,'2025-10-02 20:11:24','2025-10-05 20:55:28','2025-10-05 20:55:28'),(30,20,'sesudah','trans_pekerjaan/16b6VX8KSUKRsjb3D4fgZidLnup6CrAeMSM10qL5.jpg',NULL,0,'2025-10-02 20:12:41','2025-10-05 20:55:24','2025-10-05 20:55:24'),(31,21,'sebelum','trans_pekerjaan/hAdwdky1Ba473cM1ICA6taczjdaNbzt0Xdbm1Evl.jpg',NULL,0,'2025-10-03 00:06:56','2025-10-05 20:55:20','2025-10-05 20:55:20'),(32,21,'sesudah','trans_pekerjaan/7G6y6HJAPwGfg70wDLhOKbTjf8OayRg9D5kaVm8d.png',NULL,0,'2025-10-03 00:06:56','2025-10-05 20:55:20','2025-10-05 20:55:20'),(33,21,'sesudah','trans_pekerjaan/BZsKVkIt3kf9LRNDiq21yXbYqustLBKoKo1WqpR4.jpg',NULL,1,'2025-10-03 00:06:56','2025-10-05 20:55:20','2025-10-05 20:55:20'),(34,22,'sebelum','trans_pekerjaan/ntK9HkoUqVJYYzw4aqMwVMiPL1QTY4DLCXB8QQXV.png',NULL,0,'2025-10-03 01:25:23','2025-10-05 20:55:17','2025-10-05 20:55:17'),(35,22,'sesudah','trans_pekerjaan/sswp4IJtQDl9z0NabK715CkuWIs5Mvg6a4i1K9Sn.png',NULL,0,'2025-10-03 01:25:23','2025-10-05 20:55:17','2025-10-05 20:55:17'),(36,23,'sebelum','trans_pekerjaan/62O4igJsyP6D3lbvTAHC4oH7thCEfphhXaykSEAn.jpg',NULL,0,'2025-10-05 20:36:18','2025-10-05 20:55:05','2025-10-05 20:55:05'),(37,23,'sesudah','trans_pekerjaan/DBlTGn4TeNLNrVq5U4kAdxG4ThUGxHUtXqdH1EZq.jpg',NULL,0,'2025-10-05 20:36:18','2025-10-05 20:55:05','2025-10-05 20:55:05'),(38,24,'sebelum','trans_pekerjaan/duOglxtdTlNDkDU49PmOToH1rljBmkwrFbLuKulm.jpg',NULL,0,'2025-10-05 20:58:03','2025-10-05 20:58:28','2025-10-05 20:58:28'),(39,24,'sesudah','trans_pekerjaan/Nw2Z12GfdSVuYwQKSjSDFwGtDmZGKXdgSxcvcOrX.webp',NULL,0,'2025-10-05 20:58:03','2025-10-05 20:58:28','2025-10-05 20:58:28'),(40,25,'sebelum','trans_pekerjaan/tSR2k1GjJtVBIg0R5nJX7Ggj6Xev6TbykERV5AEj.jpg',NULL,0,'2025-10-06 18:31:58','2025-10-06 19:08:20','2025-10-06 19:08:20'),(41,25,'sesudah','trans_pekerjaan/M4r04bq2tcvKlBWlH15F002ycrPiCtKv81cen10m.jpg',NULL,0,'2025-10-06 18:31:58','2025-10-06 19:08:20','2025-10-06 19:08:20'),(42,25,'sesudah','trans_pekerjaan/TgtrIBqj4AJAAbKNv6o5lxWcJ6umGMcXh98e8S5j.jpg',NULL,1,'2025-10-06 18:31:58','2025-10-06 19:08:20','2025-10-06 19:08:20'),(43,25,'sesudah','trans_pekerjaan/eYLhaaX7gyy7J1kObGW3rizhE6UlW2kDiSvMFVTC.jpg',NULL,2,'2025-10-06 18:31:58','2025-10-06 19:08:20','2025-10-06 19:08:20'),(44,27,'sebelum','trans_pekerjaan/blufTzx9USYBde9sK33mlhgqDquWiiTvfPE2xFmO.png',NULL,0,'2025-10-06 19:02:00','2025-10-06 19:08:16','2025-10-06 19:08:16'),(45,27,'sesudah','trans_pekerjaan/g6USb251ly8i4ADTbxVvEnNh2kCOElWvuGbct1np.jpg',NULL,0,'2025-10-06 19:02:00','2025-10-06 19:08:16','2025-10-06 19:08:16'),(46,27,'sesudah','trans_pekerjaan/3a1Zvg1yxzXeh2tGudxMxM7rhPSxDDf5ujMUrGLX.jpg',NULL,1,'2025-10-06 19:02:00','2025-10-06 19:08:16','2025-10-06 19:08:16'),(47,27,'sesudah','trans_pekerjaan/lPts0D1YIXdtosTquMjIR7dCmcPB1NgQV9syvZhs.jpg',NULL,2,'2025-10-06 19:02:00','2025-10-06 19:08:16','2025-10-06 19:08:16'),(48,28,'sebelum','trans_pekerjaan/hcuCRnhXhuMYNWLJebo9qvlyOj3bydiisanuqait.jpg',NULL,0,'2025-10-06 19:07:35','2025-10-06 19:08:12','2025-10-06 19:08:12'),(49,28,'sesudah','trans_pekerjaan/uUblQLtOs9SXc1YVFOJbjzCVNmEgKaH44VSTBnbi.png',NULL,0,'2025-10-06 19:07:35','2025-10-06 19:08:12','2025-10-06 19:08:12'),(50,28,'sesudah','trans_pekerjaan/ZvTcj3TjStVzJ4n6uGcne92uVahDW3rx5lk3UrvF.jpg',NULL,1,'2025-10-06 19:07:35','2025-10-06 19:08:12','2025-10-06 19:08:12'),(51,29,'sesudah','trans_pekerjaan/kO0dzOui4XveWpI1e9rok2N2xj7Xcw8CA13qOPaR.png',NULL,0,'2025-10-06 19:20:28','2025-10-06 20:19:31','2025-10-06 20:19:31'),(52,29,'sesudah','trans_pekerjaan/w2R5PrqQGKTzF96y4SXoKGUIJG8yseg3NlVR57cv.png',NULL,1,'2025-10-06 19:20:28','2025-10-06 20:19:31','2025-10-06 20:19:31'),(53,29,'sesudah','trans_pekerjaan/A93NGbZ5sCtZgjU4CjFqEoDi53T9bKEjETxtQVh1.png',NULL,2,'2025-10-06 19:20:28','2025-10-06 20:19:31','2025-10-06 20:19:31'),(54,30,'sesudah','trans_pekerjaan/dqWY0VxIiXFA2hGHlIJGoZPrfsZqI5mDdagdrO3J.png',NULL,0,'2025-10-06 19:23:42','2025-10-06 20:19:27','2025-10-06 20:19:27'),(55,30,'sesudah','trans_pekerjaan/ttUROJW07cLoX0nhT86t3qebmfMwseFlWPIO36KW.png',NULL,1,'2025-10-06 19:23:42','2025-10-06 20:19:27','2025-10-06 20:19:27'),(56,30,'sesudah','trans_pekerjaan/CA9dqhDAFATPTiUsOzkBgfROfO4cHLPsZn077q3J.png',NULL,2,'2025-10-06 19:23:42','2025-10-06 20:19:27','2025-10-06 20:19:27'),(57,30,'sesudah','trans_pekerjaan/4URSXxAsgLfTisi81vi8pOpmEQ5zIVWQIYrX4upM.png',NULL,3,'2025-10-06 19:23:42','2025-10-06 20:19:27','2025-10-06 20:19:27'),(58,30,'sesudah','trans_pekerjaan/ZGbkrCQ5b3AhhrKpgZJOMpfPt3B7ZoKFz8SxpdvL.png',NULL,4,'2025-10-06 19:23:42','2025-10-06 20:19:27','2025-10-06 20:19:27'),(59,33,'sebelum','trans_pekerjaan/Z7FlMf0R82zCtNhDNwpv1eHlLnbbnN7SfXU6VF73.png',NULL,0,'2025-10-06 23:30:14','2025-10-24 01:42:16','2025-10-24 01:42:16'),(60,33,'sesudah','trans_pekerjaan/917ySv2xjZDQ3tSdZBONlj0cfcsPBLVdBfqfz32P.png',NULL,0,'2025-10-06 23:30:14','2025-10-24 01:42:16','2025-10-24 01:42:16'),(61,34,'sebelum','trans_pekerjaan/k3scI4qI8IKYu4Qt44yKrPDo8nuQdupevwGDFe3K.png',NULL,0,'2025-10-06 23:44:23','2025-10-06 23:50:14','2025-10-06 23:50:14'),(62,34,'sebelum','trans_pekerjaan/8Vnq9cfpIgNCzxYADkHi2xBSdoyD8TnKynGJrfhV.png',NULL,1,'2025-10-06 23:44:23','2025-11-19 01:15:54','2025-11-19 01:15:54'),(63,34,'sebelum','trans_pekerjaan/Crug8i97f3lHEcSdHuaElbF04wl22lxqrrAqEFgg.png',NULL,2,'2025-10-06 23:44:23','2025-11-19 01:15:54','2025-11-19 01:15:54'),(64,34,'sesudah','trans_pekerjaan/hmGKifxBuknj1iDcB2iN1R9ycWgb6yrL6BcJy5U4.jpg',NULL,0,'2025-10-06 23:44:23','2025-11-19 01:15:54','2025-11-19 01:15:54'),(65,34,'sebelum','trans_pekerjaan/tl92p7NZuSyO5eXlpXI71TBt7WCbyqLj36oLMuO7.png',NULL,3,'2025-10-06 23:51:26','2025-11-19 01:15:54','2025-11-19 01:15:54'),(66,35,'sebelum','trans_pekerjaan/43TTF0cl7W40cmYVRiMtI6HKXcKm5QdbwxovyRYV.jpg',NULL,0,'2025-10-07 00:07:43','2025-10-07 00:08:18','2025-10-07 00:08:18'),(67,35,'sebelum','trans_pekerjaan/4WxJRVzXfjoEdOyFXISCGJSWzQsJrZbYS2rryXBW.jpg',NULL,1,'2025-10-07 00:07:43','2025-10-07 00:08:18','2025-10-07 00:08:18'),(68,35,'sesudah','trans_pekerjaan/PPmElGpgjRsPwsqJ7M9uBtC6yIX5hFG5Ypej01tl.png',NULL,0,'2025-10-07 00:07:43','2025-10-07 00:08:18','2025-10-07 00:08:18'),(69,35,'sesudah','trans_pekerjaan/i8a0doegfkpCC3NzJxyzmUrzHwEOKik3lgM4yp5S.png',NULL,1,'2025-10-07 00:07:43','2025-10-07 00:08:18','2025-10-07 00:08:18'),(70,42,'sebelum','trans_pekerjaan/DmD1dHFk1w8P6uGCWJFolo5oKoxY7RDRUtmbza5Z.png',NULL,0,'2025-10-07 03:06:18','2025-10-24 01:42:02','2025-10-24 01:42:02'),(71,42,'sesudah','trans_pekerjaan/SHprjifRh0wEcelyRod5RoZRDgp9ze7Jx7VbOkUA.png',NULL,0,'2025-10-07 03:06:18','2025-10-24 01:42:02','2025-10-24 01:42:02'),(72,43,'sesudah','trans_pekerjaan/PGChFpj0uxDgsurLHd0G9aHZDceGBnqxOB73o5Dh.png',NULL,0,'2025-10-07 18:18:02','2025-10-24 01:41:55','2025-10-24 01:41:55'),(73,43,'sesudah','trans_pekerjaan/oWqeTmFtn2gvaQe7sJy1XHW6eR3iJVNlAqumvZkR.png',NULL,1,'2025-10-07 18:18:02','2025-10-24 01:41:55','2025-10-24 01:41:55'),(74,43,'sesudah','trans_pekerjaan/zZs3C0wRNac1qpKT8fY4PmOlvmf7H6jJcN84qfdc.png',NULL,2,'2025-10-07 18:18:02','2025-10-24 01:41:55','2025-10-24 01:41:55'),(75,43,'sesudah','trans_pekerjaan/2WNPMlv4L8uqMIGmWClDUck0ilzVAG4oP5w1yzNT.png',NULL,3,'2025-10-07 18:18:02','2025-10-24 01:41:55','2025-10-24 01:41:55'),(76,44,'sebelum','trans_pekerjaan/9tNpcWP5gbrIUqT1nvbNxqm9K2Y0u1gWnyitPsZY.jpg',NULL,0,'2025-10-07 20:21:33','2025-10-24 01:41:50','2025-10-24 01:41:50'),(77,44,'sebelum','trans_pekerjaan/I0zVIipZW0LGwo47BUBM9JqbjqlcXVV73zufZh3C.png',NULL,1,'2025-10-07 20:21:33','2025-10-24 01:41:50','2025-10-24 01:41:50'),(78,44,'sesudah','trans_pekerjaan/lytT4anxYqOQvokQlumAzMxdoQhu7nsfw58JXlqU.png',NULL,0,'2025-10-07 20:21:33','2025-10-07 23:40:55','2025-10-07 23:40:55'),(79,44,'sesudah','trans_pekerjaan/YvHg3H1cp5PL2PCANm90WP4HWOBSOuDXom6UeRoD.png',NULL,0,'2025-10-07 23:41:12','2025-10-24 01:41:50','2025-10-24 01:41:50'),(80,45,'sesudah','trans_pekerjaan/bkdqt9OJPAFB2SL6eT5lebfd2gUc2OnJQIO95dI0.png',NULL,0,'2025-10-08 00:10:23','2025-11-19 01:15:47','2025-11-19 01:15:47'),(81,46,'sebelum','trans_pekerjaan/bYgANbTKD3LvRLMvKhNuu8hN9c5xSnc6hbi7UbkU.png',NULL,0,'2025-10-08 01:32:38','2025-10-24 01:41:40','2025-10-24 01:41:40'),(82,46,'sesudah','trans_pekerjaan/3yiLWAoauLhdssK7gG96IuMgTuzgWaENg5MHcnWU.png',NULL,0,'2025-10-08 01:32:38','2025-10-24 01:41:40','2025-10-24 01:41:40'),(83,46,'sebelum','trans_pekerjaan/zOLhg3GMyN6pspxIVH0CdZAaorpyVAGOyEiOloZf.png',NULL,1,'2025-10-08 01:35:49','2025-10-24 01:41:40','2025-10-24 01:41:40'),(84,47,'sebelum','trans_pekerjaan/f_1_691c25e756c94.png',NULL,0,'2025-11-18 07:53:11','2025-11-18 07:55:57','2025-11-18 07:55:57'),(85,47,'sesudah','trans_pekerjaan/f_1_691c25e7588e4.png',NULL,0,'2025-11-18 07:53:11','2025-11-18 07:55:57','2025-11-18 07:55:57'),(86,48,'sebelum','trans_pekerjaan/f_4_691c25e759bb4.png',NULL,0,'2025-11-18 07:53:11','2025-11-19 01:15:41','2025-11-19 01:15:41'),(87,48,'sesudah','trans_pekerjaan/f_4_691c25e75a931.png',NULL,0,'2025-11-18 07:53:11','2025-11-19 01:15:41','2025-11-19 01:15:41'),(88,49,'sebelum','trans_pekerjaan/xJmDoSM0zuQhmfKskMEdallasGN0Vvh9nzB5r8D4.png',NULL,0,'2025-11-18 08:21:36','2025-11-19 01:15:34','2025-11-19 01:15:34'),(89,49,'sesudah','trans_pekerjaan/RKi091ImIE7zME2NOWhAzd5CT5vOorP9EMVZdtnf.png',NULL,0,'2025-11-18 08:21:36','2025-11-19 01:15:34','2025-11-19 01:15:34'),(90,50,'sebelum','trans_pekerjaan/njwrcAGg6ugjz9da11pPO9vThCDFc9X9onx2bQhz.png',NULL,0,'2025-11-19 01:34:35','2025-11-19 03:18:16','2025-11-19 03:18:16'),(91,50,'sesudah','trans_pekerjaan/mI87cePMGGiXilYSugLByYMFCnzz6MundN6foiyM.png',NULL,0,'2025-11-19 01:34:35','2025-11-19 03:18:16','2025-11-19 03:18:16'),(92,51,'sebelum','trans_pekerjaan/8SMHltcQZETmbD3VVOHc2l7DRbOnilqr0ZiPAEUF.png',NULL,0,'2025-11-19 01:50:56','2025-11-19 03:18:11','2025-11-19 03:18:11'),(93,51,'sesudah','trans_pekerjaan/2O4AUwqjl3XjforZNPzQLBPnPOdiXzqSzdp0fnm2.png',NULL,0,'2025-11-19 01:50:56','2025-11-19 03:18:11','2025-11-19 03:18:11'),(94,52,'sebelum','trans_pekerjaan/B1ndVNkBBLH55aYnfntW9w769WqytQlz8JXKNCVX.png',NULL,0,'2025-11-19 03:12:26','2025-11-19 03:18:07','2025-11-19 03:18:07'),(95,52,'sesudah','trans_pekerjaan/MMKmYcEJUuqkLxY7WSc8zHOLwpLOhNWjxD4Y4SZR.png',NULL,0,'2025-11-19 03:12:26','2025-11-19 03:18:07','2025-11-19 03:18:07'),(96,53,'sebelum','trans_pekerjaan/NGqORsW5voqGLyIRflx6yUcTXzlA5wtuvbzd9QMg.png',NULL,0,'2025-11-19 03:19:43','2025-11-19 03:19:43',NULL),(97,53,'sesudah','trans_pekerjaan/kLcmY6aB6T2TF9x9kpcAIhyL4QwpjkmwZw3U07TN.png',NULL,0,'2025-11-19 03:19:43','2025-11-19 03:19:43',NULL),(98,54,'sebelum','trans_pekerjaan/l4xVLzwXr5soFC4hRSyHEa2YyWAwoWnmkXxQFO51.png',NULL,0,'2025-11-19 03:51:45','2025-11-19 03:51:45',NULL),(99,54,'sesudah','trans_pekerjaan/dnLFEkhIVwgsNiBYuafBLsOmCs9WTkWIWmwyf3SO.png',NULL,0,'2025-11-19 03:51:45','2025-11-19 03:51:45',NULL);

#
# Structure for table "trans_pekerjaan_pegawai"
#

CREATE TABLE `trans_pekerjaan_pegawai` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pekerjaan_id` bigint unsigned NOT NULL,
  `pegawai_id` bigint unsigned NOT NULL,
  `id_divisi` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trans_pekerjaan_pegawai_pekerjaan_id_pegawai_id_unique` (`pekerjaan_id`,`pegawai_id`),
  KEY `trans_pekerjaan_pegawai_pegawai_id_foreign` (`pegawai_id`),
  KEY `trans_pekerjaan_pegawai_id_divisi_foreign` (`id_divisi`),
  CONSTRAINT `trans_pekerjaan_pegawai_id_divisi_foreign` FOREIGN KEY (`id_divisi`) REFERENCES `master_divisi` (`id_divisi`) ON DELETE SET NULL,
  CONSTRAINT `trans_pekerjaan_pegawai_pegawai_id_foreign` FOREIGN KEY (`pegawai_id`) REFERENCES `master_pegawai` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `trans_pekerjaan_pegawai_pekerjaan_id_foreign` FOREIGN KEY (`pekerjaan_id`) REFERENCES `trans_pekerjaan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "trans_pekerjaan_pegawai"
#

INSERT INTO `trans_pekerjaan_pegawai` VALUES (1,49,1,NULL,'2025-11-18 08:21:36','2025-11-18 08:21:36'),(2,49,4,NULL,'2025-11-18 08:21:36','2025-11-18 08:21:36'),(3,50,1,NULL,'2025-11-19 01:34:33','2025-11-19 01:34:33'),(4,50,9,NULL,'2025-11-19 01:34:33','2025-11-19 01:34:33'),(5,51,1,NULL,'2025-11-19 01:50:56','2025-11-19 01:50:56'),(6,52,1,1,NULL,NULL),(7,52,9,5,NULL,NULL),(8,53,1,1,NULL,NULL),(9,53,2,4,NULL,NULL),(12,54,3,4,NULL,NULL),(13,54,12,1,NULL,NULL);

#
# Structure for table "users"
#

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `about` text COLLATE utf8mb4_unicode_ci,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "users"
#

INSERT INTO `users` VALUES (1,'admin','Admin','Admin','admin@argon.com',NULL,'$2y$12$DEJ9oyCB6Vga1n0UPDDgdeAu/3f1dvB5f64hXsAdJZI8plxD2hU6u',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,'10012','Yoga Febrian','Maulana','10012@pegawai.local',NULL,'$2y$12$YXs4F0YM32ZQukEfp4Q2leieQcSiv2X9Anx9uQ9LTYlSyc76YLOMG',NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-02 04:11:16','2025-10-06 02:47:13'),(3,'10006','Linggar','Pangestu','10006@pegawai.local',NULL,'$2y$12$9dkRR/J7REqpPOtRMJrhtus.PAFCE93z7nF.Jx1Uj1cH9vQITlCgi',NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-02 04:12:28','2025-10-02 07:23:43'),(4,'10003','Afandy Tumpal','Halomoan','10003@pegawai.local',NULL,'$2y$12$O5kl0Uuc4FLJgFpsdBylN.ySM/sSExooFsNVz6Nxfrw19ZM.uqVwu',NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-02 06:41:57','2025-10-02 07:23:44'),(5,'10004','Indra','Armaulana','10004@pegawai.local',NULL,'$2y$12$iZ0ddTfmG6ZPKDvfrh3Hduu2aT0A6PHz47vlhDwyNuK.1CruiBEwe',NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-02 06:42:20','2025-10-02 07:23:44'),(6,'10005','Indra','Kurniawan','10005@pegawai.local',NULL,'$2y$12$ISF83QX/uZJXLlKgUlI4hu5/u/uKfw3z6U1bVQQxmbfzyX29HZlQ6',NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-02 06:42:41','2025-10-02 07:23:44'),(7,'10007','Nindy','Permatasari','10007@pegawai.local',NULL,'$2y$12$4I1g0cNv9EBBGRHEjHF4NO3I1QtLpv5Aib6IlMyoGsIPzfTFnNwAS',NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-02 06:43:04','2025-10-02 07:23:45'),(8,'10008','Sandi','Saputra','10008@pegawai.local',NULL,'$2y$12$ZnoCKLDeaa0akgHKrAQDTOfGdmsHvCOt6QphZg1rR0Q4who1W0SLy',NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-02 06:43:26','2025-10-02 07:23:45'),(9,'10009','Saharudin',NULL,'10009@pegawai.local',NULL,'$2y$12$khijpYNNUxjJT7B5qVpoguvQwkHHTLW8bNdcTn3wXjUVuot9PZQkK',NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-02 06:43:46','2025-10-02 07:23:46'),(10,'10011','Tommy Wijaya','Putra','10011@pegawai.local',NULL,'$2y$12$aC7uTKpZcxYs88TV3mvOmejXeS2wwAA7FEPr9fYDRDMQJJoD0fuTS',NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-02 06:44:18','2025-10-02 07:23:46'),(11,'10101001','test',NULL,'10101001@pegawai.local',NULL,'$2y$12$N8SNZA7EIZEU3H6bUosjUuevr6QLk3MKXjoSGjYR02R8uzL8S8Fcq',NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-02 06:45:29','2025-10-02 07:23:46'),(12,'PGW001','All Team',NULL,'PGW001@pegawai.local',NULL,'$2y$12$5QV9VsoFnfZp/j3em4lYNOmDE.Zapmr7Lu67MrR9c/88Uj.vm4xPS',NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-06 03:57:40','2025-10-06 04:02:52'),(13,'10010','Suharyadi',NULL,'10010@pegawai.local',NULL,'$2y$12$0tkpNytkLsayKlZFM/.d..MupqPkUI.s3NJ4sSk55Ys2zFQXGVX/G',NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:12:54','2025-10-07 02:12:54'),(14,'10013','Hafidz Rachmat Riandi',NULL,'10013@pegawai.local',NULL,'$2y$12$kaGt7LyzWFndiekin5c3L.fPMfjse7.kaLvr1WSDLsGk78b.jBwEi',NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:14:57','2025-10-07 02:14:57'),(15,'10000','All Team',NULL,'10000@pegawai.local',NULL,'$2y$12$FLJ641QWjT2vUpKLa6AQU.zMDOQEO.LkKsF5iHpltCob4q2FP.lc.',NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:17:04','2025-10-07 02:17:04');
