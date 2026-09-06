import 'package:flutter/material.dart';

import '../models/news_item.dart';
import '../screens/news/news_screen.dart';
import '../services/api_service.dart';
import '../theme.dart';
import 'article_reader_sheet.dart';

class NewsDashboardCard extends StatefulWidget {
  final List<NewsItem> initialNews;
  final VoidCallback? onRefresh;

  const NewsDashboardCard({
    super.key,
    this.initialNews = const [],
    this.onRefresh,
  });

  @override
  State<NewsDashboardCard> createState() => _NewsDashboardCardState();
}

class _NewsDashboardCardState extends State<NewsDashboardCard> {
  List<NewsItem> _news = [];
  bool _loading = false;

  @override
  void initState() {
    super.initState();
    _news = List.from(widget.initialNews);
    if (_news.isEmpty) {
      _fetchNews();
    }
  }

  @override
  void didUpdateWidget(covariant NewsDashboardCard oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.initialNews.isNotEmpty && widget.initialNews != oldWidget.initialNews) {
      setState(() {
        _news = List.from(widget.initialNews);
      });
    }
  }

  Future<void> _fetchNews() async {
    if (_loading) return;
    setState(() => _loading = true);
    try {
      final res = await ApiService.instance.getNews(limit: 8);
      final rawHeadlines = res['headlines'] ?? res['items'] ?? [];
      if (rawHeadlines is List && mounted) {
        setState(() {
          _news = rawHeadlines
              .map((e) => NewsItem.fromJson(e as Map<String, dynamic>))
              .toList();
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
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

  void _openArticleSheet(NewsItem item) {
    ArticleReaderSheet.show(context, item);
  }

  @override
  Widget build(BuildContext context) {
    if (_news.isEmpty && !_loading) {
      return const SizedBox.shrink();
    }

    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Row
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Text('📰', style: TextStyle(fontSize: 16)),
                  const SizedBox(width: 8),
                  Text(
                    'Berita Terkini',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w800,
                      color: isDark ? Colors.white : AppColors.textPrimary,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEF4444),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        CircleAvatar(radius: 2.5, backgroundColor: Colors.white),
                        SizedBox(width: 4),
                        Text(
                          'LIVE',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 9,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              InkWell(
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const NewsScreen()),
                  );
                },
                borderRadius: BorderRadius.circular(8),
                child: const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'Selengkapnya',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w800,
                          color: AppColors.primary,
                        ),
                      ),
                      SizedBox(width: 2),
                      Icon(Icons.chevron_right, size: 16, color: AppColors.primary),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // Horizontal Feed
          if (_loading && _news.isEmpty)
            const SizedBox(
              height: 150,
              child: Center(child: CircularProgressIndicator(strokeWidth: 2)),
            )
          else
            SizedBox(
              height: 180,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: _news.length,
                separatorBuilder: (context, index) => const SizedBox(width: 12),
                itemBuilder: (ctx, idx) {
                  final it = _news[idx];
                  return InkWell(
                    onTap: () => _openArticleSheet(it),
                    borderRadius: BorderRadius.circular(14),
                    child: Container(
                      width: 220,
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(
                          color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                        ),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Thumbnail
                          ClipRRect(
                            borderRadius: const BorderRadius.vertical(top: Radius.circular(13)),
                            child: SizedBox(
                              width: double.infinity,
                              height: 90,
                              child: it.hasImage && it.image.isNotEmpty
                                  ? Image.network(
                                      it.image,
                                      fit: BoxFit.cover,
                                      errorBuilder: (context, error, stackTrace) => Container(
                                        color: const Color(0xFF1E293B),
                                        child: Center(
                                          child: Text(it.icon, style: const TextStyle(fontSize: 24)),
                                        ),
                                      ),
                                    )
                                  : Container(
                                      color: const Color(0xFF1E293B),
                                      child: Center(
                                        child: Text(it.icon, style: const TextStyle(fontSize: 24)),
                                      ),
                                    ),
                            ),
                          ),
                          // Body
                          Padding(
                            padding: const EdgeInsets.all(8.0),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                                      decoration: BoxDecoration(
                                        color: _parseHexColor(it.color),
                                        borderRadius: BorderRadius.circular(4),
                                      ),
                                      child: Text(
                                        it.source,
                                        style: const TextStyle(
                                          color: Colors.white,
                                          fontSize: 9,
                                          fontWeight: FontWeight.w800,
                                        ),
                                      ),
                                    ),
                                    Text(
                                      it.timeAgo,
                                      style: TextStyle(
                                        fontSize: 9.5,
                                        color: isDark ? Colors.white60 : Colors.black54,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 6),
                                Text(
                                  it.title,
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                  style: TextStyle(
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.w800,
                                    color: isDark ? Colors.white : AppColors.textPrimary,
                                    height: 1.25,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
        ],
      ),
    );
  }
}
