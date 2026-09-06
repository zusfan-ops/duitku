import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../models/news_item.dart';
import '../services/api_service.dart';
import '../theme.dart';

class ArticleReaderSheet extends StatefulWidget {
  final NewsItem item;

  const ArticleReaderSheet({
    super.key,
    required this.item,
  });

  static void show(BuildContext context, NewsItem item) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useRootNavigator: true,
      backgroundColor: Colors.transparent,
      builder: (_) => ArticleReaderSheet(item: item),
    );
  }

  @override
  State<ArticleReaderSheet> createState() => _ArticleReaderSheetState();
}

class _ArticleReaderSheetState extends State<ArticleReaderSheet> {
  List<String> _paragraphs = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _fetchFullArticle();
  }

  Future<void> _fetchFullArticle() async {
    try {
      final res = await ApiService.instance.getArticleContent(widget.item.link);
      if (!mounted) return;
      if (res['success'] == true && res['paragraphs'] is List) {
        setState(() {
          _paragraphs = (res['paragraphs'] as List).map((e) => e.toString()).toList();
          _loading = false;
        });
      } else {
        setState(() {
          _loading = false;
        });
      }
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _loading = false;
      });
    }
  }

  Color _parseHexColor(String hex, {Color fallback = AppColors.primary}) {
    try {
      var s = hex.replaceAll('#', '');
      if (s.length == 6) s = 'FF$s';
      return Color(int.parse(s, radix: 16));
    } catch (_) {
      return fallback;
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final item = widget.item;

    return DraggableScrollableSheet(
      initialChildSize: 0.88,
      minChildSize: 0.5,
      maxChildSize: 0.95,
      builder: (ctx, scrollController) {
        return Container(
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF1E293B) : Colors.white,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.2),
                blurRadius: 20,
                offset: const Offset(0, -5),
              ),
            ],
          ),
          child: Column(
            children: [
              // Top Drag Handle & Bar
              Container(
                padding: const EdgeInsets.fromLTRB(18, 10, 14, 10),
                decoration: BoxDecoration(
                  border: Border(
                    bottom: BorderSide(
                      color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                    ),
                  ),
                ),
                child: Column(
                  children: [
                    Center(
                      child: Container(
                        width: 38,
                        height: 4,
                        decoration: BoxDecoration(
                          color: isDark ? Colors.white24 : Colors.black12,
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
                          decoration: BoxDecoration(
                            color: _parseHexColor(item.color),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            item.source,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                        Row(
                          children: [
                            IconButton(
                              icon: const Icon(Icons.share_outlined, size: 18),
                              tooltip: 'Bagikan',
                              onPressed: () async {
                                final uri = Uri.tryParse(item.link);
                                if (uri != null && await canLaunchUrl(uri)) {
                                  await launchUrl(uri, mode: LaunchMode.externalApplication);
                                }
                              },
                            ),
                            IconButton(
                              icon: const Icon(Icons.close, size: 20),
                              tooltip: 'Tutup',
                              onPressed: () => Navigator.pop(context),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              // Scrollable Article Content
              Expanded(
                child: ListView(
                  controller: scrollController,
                  padding: EdgeInsets.fromLTRB(20, 16, 20, 36 + MediaQuery.of(context).padding.bottom),
                  children: [
                    // Meta: Date & Category
                    Row(
                      children: [
                        Text(
                          '${item.category} • ${item.timeAgo}',
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w700,
                            color: isDark ? Colors.white60 : Colors.black54,
                          ),
                        ),
                        if (item.pubDate.isNotEmpty) ...[
                          const SizedBox(width: 6),
                          Text(
                            '(${item.pubDate})',
                            style: TextStyle(
                              fontSize: 10.5,
                              color: isDark ? Colors.white38 : Colors.black38,
                            ),
                          ),
                        ],
                      ],
                    ),
                    const SizedBox(height: 10),

                    // Article Title
                    Text(
                      item.title,
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w800,
                        color: isDark ? Colors.white : AppColors.textPrimary,
                        height: 1.3,
                        letterSpacing: -0.3,
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Hero Image
                    if (item.hasImage && item.image.isNotEmpty)
                      ClipRRect(
                        borderRadius: BorderRadius.circular(16),
                        child: AspectRatio(
                          aspectRatio: 16 / 9,
                          child: Image.network(
                            item.image,
                            fit: BoxFit.cover,
                            errorBuilder: (context, error, stackTrace) => const SizedBox.shrink(),
                          ),
                        ),
                      ),
                    const SizedBox(height: 14),

                    // Initial Excerpt Block
                    if (item.description.isNotEmpty)
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
                          borderRadius: BorderRadius.circular(12),
                          border: Border(
                            left: BorderSide(
                              color: _parseHexColor(item.color),
                              width: 3.5,
                            ),
                          ),
                        ),
                        child: Text(
                          item.description,
                          style: TextStyle(
                            fontSize: 13,
                            fontStyle: FontStyle.italic,
                            color: isDark ? Colors.white70 : Colors.black87,
                            height: 1.45,
                          ),
                        ),
                      ),
                    const SizedBox(height: 16),

                    // Full Article Body
                    if (_loading)
                      Container(
                        padding: const EdgeInsets.symmetric(vertical: 24),
                        child: Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const SizedBox(
                                width: 22,
                                height: 22,
                                child: CircularProgressIndicator(strokeWidth: 2.2, color: AppColors.primary),
                              ),
                              const SizedBox(height: 12),
                              Text(
                                'Memuat seluruh naskah berita...',
                                style: TextStyle(
                                  fontSize: 12.5,
                                  color: isDark ? Colors.white54 : Colors.black54,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                        ),
                      )
                    else if (_paragraphs.isNotEmpty)
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: _paragraphs.map((p) {
                          return Padding(
                            padding: const EdgeInsets.only(bottom: 14),
                            child: Text(
                              p,
                              style: TextStyle(
                                fontSize: 14.5,
                                height: 1.75,
                                color: isDark ? const Color(0xFFE2E8F0) : const Color(0xFF1E293B),
                                letterSpacing: 0.1,
                              ),
                            ),
                          );
                        }).toList(),
                      )
                    else
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        child: Text(
                          item.description,
                          style: TextStyle(
                            fontSize: 14.5,
                            height: 1.75,
                            color: isDark ? const Color(0xFFE2E8F0) : const Color(0xFF1E293B),
                          ),
                        ),
                      ),

                    const SizedBox(height: 24),

                    // In-App Action: Done Button & Source Credit
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 13),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          elevation: 0,
                        ),
                        onPressed: () => Navigator.pop(context),
                        child: const Text(
                          'Selesai Membaca',
                          style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5),
                        ),
                      ),
                    ),
                    const SizedBox(height: 10),
                    Center(
                      child: GestureDetector(
                        onTap: () async {
                          final uri = Uri.tryParse(item.link);
                          if (uri != null && await canLaunchUrl(uri)) {
                            await launchUrl(uri, mode: LaunchMode.externalApplication);
                          }
                        },
                        child: Text(
                          'Dikutip dari ${item.source} • Kunjungi Halaman Asli',
                          style: TextStyle(
                            fontSize: 11,
                            color: isDark ? Colors.white38 : Colors.black38,
                            decoration: TextDecoration.underline,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
