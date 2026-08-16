import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../theme.dart';

class DeveloperScreen extends StatelessWidget {
  const DeveloperScreen({super.key});

  Future<void> _openUrl(BuildContext context, String urlStr) async {
    final uri = Uri.parse(urlStr);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Tidak dapat membuka $urlStr')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Informasi Developer'),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 40),
        children: [
          // Hero Profile Card
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF064E3B), Color(0xFF059669)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(24),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF059669).withValues(alpha: 0.35),
                  blurRadius: 16,
                  offset: const Offset(0, 6),
                ),
              ],
            ),
            child: Column(
              children: [
                Container(
                  width: 90,
                  height: 90,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    border: Border.all(color: Colors.white, width: 3),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.2),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: ClipOval(
                    child: Image.network(
                      'https://zusfan.hallosemarang.com/DSC00218.jpg',
                      fit: BoxFit.cover,
                      errorBuilder: (_, _, _) => Container(
                        color: const Color(0xFF2D5A27),
                        alignment: Alignment.center,
                        child: const Text('ZM', style: TextStyle(color: Colors.white, fontSize: 28, fontWeight: FontWeight.w900)),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                const Text(
                  'Zusfan Mashuri',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                    color: Colors.white,
                    letterSpacing: -0.3,
                  ),
                ),
                const SizedBox(height: 4),
                const Text(
                  'Marketing Strategist · IT Builder · Public Service Innovator',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFFD1FAE5),
                  ),
                ),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Text(
                    'Founder & Marketing IT Director @ Hallo Semarang',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.white),
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  'Pengembang sistem digital dengan pengalaman di marketing strategi, infrastruktur IT, smart city, dan pemberdayaan UMKM & komunitas melalui teknologi.',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.white.withValues(alpha: 0.9),
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Action Buttons (WhatsApp, Digital Card, Website)
          Row(
            children: [
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () => _openUrl(context, 'https://wa.me/628998813000'),
                  icon: const Text('💬', style: TextStyle(fontSize: 16)),
                  label: const Text('WhatsApp', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF25D366),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () => _openUrl(context, 'https://zusfan.hallosemarang.com/'),
                  icon: const Text('🌐', style: TextStyle(fontSize: 16)),
                  label: const Text('Digital Card', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF1E293B),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Achievements Section
          _buildSectionCard(
            title: '🎯 Pencapaian Highlight',
            children: [
              _buildBulletItem('🚀', 'Hallo Semarang', 'Mengembangkan platform berita digital dengan 100,000+ pembaca bulanan & pertumbuhan 200% dalam 6 bulan.'),
              _buildBulletItem('🌐', 'Smart City Semarang', 'Implementasi WiFi gratis di 50+ lokasi publik untuk masyarakat.'),
              _buildBulletItem('📡', 'Infrastruktur Media', 'Membangun TV streaming (GETTV) di Lombok & videotron centralized di Alun-Alun Klaten.'),
              _buildBulletItem('🤝', 'Pemberdayaan UMKM', 'Memfasilitasi digitalisasi UMKM & pengembangan komunitas melalui teknologi.'),
            ],
          ),
          const SizedBox(height: 12),

          // Experience Section
          _buildSectionCard(
            title: '💼 Pengalaman Profesional',
            children: [
              _buildExpRow('Marketing & IT Director', 'Hallo Semarang', '2020 - Sekarang'),
              const Divider(height: 16),
              _buildExpRow('Pemberdayaan Digital UMKM', 'Gang SMP 14 Banua Anyar', '2019'),
              const Divider(height: 16),
              _buildExpRow('IT Solutions Consultant', 'NTB & Bali Region', '2012 - 2015'),
            ],
          ),
          const SizedBox(height: 12),

          // Tech Stack Skills
          _buildSectionCard(
            title: '🛠️ Keahlian & Teknologi',
            children: [
              Wrap(
                spacing: 6,
                runSpacing: 6,
                children: [
                  _buildSkillChip('Flutter & Dart'),
                  _buildSkillChip('CodeIgniter 4 / PHP'),
                  _buildSkillChip('MySQL & Database'),
                  _buildSkillChip('JavaScript / React'),
                  _buildSkillChip('Node.js'),
                  _buildSkillChip('Smart City & IoT'),
                  _buildSkillChip('SEO & Digital Marketing'),
                  _buildSkillChip('Docker & Cloud AWS'),
                ],
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Portfolio Links
          _buildSectionCard(
            title: '🔗 Tautan Profil & Portofolio',
            children: [
              _buildLinkTile(context, '🌐 Website Hallo Semarang', 'hallosemarang.com', 'https://hallosemarang.com'),
              _buildLinkTile(context, '📄 Resume & CV', 'zusfan.hallosemarang.com/resume.html', 'https://zusfan.hallosemarang.com/resume.html'),
              _buildLinkTile(context, '💼 Portofolio Project', 'zusfan.hallosemarang.com/projects.html', 'https://zusfan.hallosemarang.com/projects.html'),
              _buildLinkTile(context, '🎓 Riwayat Pendidikan', 'zusfan.hallosemarang.com/education.html', 'https://zusfan.hallosemarang.com/education.html'),
            ],
          ),
          const SizedBox(height: 20),

          // Footer
          const Center(
            child: Text(
              'DuitKu · Made with ❤️ in Indonesia\nby Zusfan Mashuri',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 11, color: AppColors.textMuted, height: 1.4),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionCard({required String title, required List<Widget> children}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
          const SizedBox(height: 12),
          ...children,
        ],
      ),
    );
  }

  Widget _buildBulletItem(String icon, String title, String desc) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(icon, style: const TextStyle(fontSize: 16)),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                const SizedBox(height: 2),
                Text(desc, style: const TextStyle(fontSize: 11.5, color: AppColors.textSecondary, height: 1.3)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildExpRow(String role, String org, String period) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(role, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            Text(org, style: const TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
          ],
        ),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
          decoration: BoxDecoration(
            color: AppColors.bg,
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: AppColors.border),
          ),
          child: Text(period, style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
        ),
      ],
    );
  }

  Widget _buildSkillChip(String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: AppColors.primarySubtle,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.primary.withValues(alpha: 0.2)),
      ),
      child: Text(
        label,
        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.primaryDark),
      ),
    );
  }

  Widget _buildLinkTile(BuildContext context, String title, String subtitle, String url) {
    return InkWell(
      onTap: () => _openUrl(context, url),
      borderRadius: BorderRadius.circular(10),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                  Text(subtitle, style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                ],
              ),
            ),
            const Icon(Icons.arrow_outward_rounded, size: 16, color: AppColors.primary),
          ],
        ),
      ),
    );
  }
}
