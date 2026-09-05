import 'package:flutter/material.dart';
import '../../config/api_config.dart';

class ChatMediaHelpers {
  static const List<String> smileyEmojis = [
    '😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃',
    '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😋',
    '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐',
    '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥', '😌',
    '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧',
    '🥵', '🥶', '🥴', '😵', '🤯', '🤠', '🥳', '🥸', '😎', '🤓',
    '🧐', '😕', '😟', '🙁', '😮', '😯', '😲', '😳', '🥺', '😭',
  ];

  static const List<String> handEmojis = [
    '👍', '👎', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✌️', '🤞',
    '🤟', '🤘', '🤙', '👈', '👉', '👆', '👇', '☝️', '✋', '🤚',
    '🖐️', '🖖', '👋', '✍️', '💪', '🤳', '💅', '👀', '👁️', '👄',
  ];

  static const List<String> heartSymbols = [
    '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔',
    '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '🔥',
    '⭐', '🌟', '✨', '⚡', '💯', '💥', '🎉', '🎊', '🎁', '💰',
    '💵', '💸', '📦', '🛒', '🏷️', '✅', '❌', '⚠️', '🔔', '💬',
  ];

  static void showEmojiPicker(
    BuildContext context, {
    required ValueChanged<String> onEmojiSelected,
  }) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    showModalBottomSheet(
      context: context,
      backgroundColor: isDark ? const Color(0xFF1F2C34) : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return DefaultTabController(
          length: 3,
          child: SizedBox(
            height: 320,
            child: Column(
              children: [
                Container(
                  margin: const EdgeInsets.only(top: 8, bottom: 4),
                  width: 36,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.withValues(alpha: 0.4),
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
                TabBar(
                  labelColor: const Color(0xFF00A884),
                  unselectedLabelColor: Colors.grey,
                  indicatorColor: const Color(0xFF00A884),
                  indicatorWeight: 3,
                  tabs: const [
                    Tab(icon: Text('😊', style: TextStyle(fontSize: 18)), text: 'Wajah'),
                    Tab(icon: Text('👍', style: TextStyle(fontSize: 18)), text: 'Tangan'),
                    Tab(icon: Text('❤️', style: TextStyle(fontSize: 18)), text: 'Simbol'),
                  ],
                ),
                Expanded(
                  child: TabBarView(
                    children: [
                      _buildEmojiGrid(smileyEmojis, onEmojiSelected),
                      _buildEmojiGrid(handEmojis, onEmojiSelected),
                      _buildEmojiGrid(heartSymbols, onEmojiSelected),
                    ],
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  static Widget _buildEmojiGrid(List<String> list, ValueChanged<String> onSelected) {
    return GridView.builder(
      padding: const EdgeInsets.all(12),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 7,
        crossAxisSpacing: 8,
        mainAxisSpacing: 8,
      ),
      itemCount: list.length,
      itemBuilder: (context, index) {
        final emoji = list[index];
        return InkWell(
          borderRadius: BorderRadius.circular(8),
          onTap: () {
            onSelected(emoji);
          },
          child: Center(
            child: Text(
              emoji,
              style: const TextStyle(fontSize: 24),
            ),
          ),
        );
      },
    );
  }

  static void showAttachmentPicker(
    BuildContext context, {
    required VoidCallback onCamera,
    required VoidCallback onGallery,
  }) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    showModalBottomSheet(
      context: context,
      backgroundColor: isDark ? const Color(0xFF1F2C34) : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                _buildAttachOption(
                  icon: Icons.camera_alt_rounded,
                  label: 'Kamera',
                  color: const Color(0xFFD3396D),
                  onTap: () {
                    Navigator.pop(ctx);
                    onCamera();
                  },
                ),
                _buildAttachOption(
                  icon: Icons.photo_library_rounded,
                  label: 'Galeri Foto',
                  color: const Color(0xFFAC44CF),
                  onTap: () {
                    Navigator.pop(ctx);
                    onGallery();
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  static Widget _buildAttachOption({
    required IconData icon,
    required String label,
    required Color color,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            CircleAvatar(
              radius: 28,
              backgroundColor: color,
              child: Icon(icon, color: Colors.white, size: 28),
            ),
            const SizedBox(height: 8),
            Text(
              label,
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
            ),
          ],
        ),
      ),
    );
  }

  static bool isImageMessage(String msg) {
    return msg.trim().startsWith('[img:');
  }

  static String extractImageUrl(String msg) {
    final trimmed = msg.trim();
    final start = trimmed.indexOf('[img:');
    if (start == -1) return '';
    final end = trimmed.indexOf(']', start);
    if (end == -1) return '';
    final rawUrl = trimmed.substring(start + 5, end).trim();
    if (rawUrl.startsWith('http')) return rawUrl;
    return '${ApiConfig.baseUrl}$rawUrl';
  }

  static String extractCaption(String msg) {
    final trimmed = msg.trim();
    final end = trimmed.indexOf(']');
    if (end == -1 || end == trimmed.length - 1) return '';
    return trimmed.substring(end + 1).trim();
  }

  static void showImagePreview(BuildContext context, String fullUrl) {
    showDialog(
      context: context,
      barrierColor: Colors.black87,
      builder: (ctx) {
        return Scaffold(
          backgroundColor: Colors.transparent,
          appBar: AppBar(
            backgroundColor: Colors.transparent,
            elevation: 0,
            iconTheme: const IconThemeData(color: Colors.white),
            title: const Text('Foto', style: TextStyle(color: Colors.white, fontSize: 16)),
          ),
          body: Center(
            child: InteractiveViewer(
              panEnabled: true,
              minScale: 0.8,
              maxScale: 4.0,
              child: Image.network(
                fullUrl,
                fit: BoxFit.contain,
                loadingBuilder: (context, child, progress) {
                  if (progress == null) return child;
                  return const Center(child: CircularProgressIndicator(color: Colors.white));
                },
                errorBuilder: (context, error, stackTrace) => const Center(
                  child: Text('Gagal memuat gambar', style: TextStyle(color: Colors.white)),
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}
