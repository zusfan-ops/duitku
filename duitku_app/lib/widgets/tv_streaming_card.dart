import 'package:flutter/material.dart';
import 'package:video_player/video_player.dart';

import '../models/tv_channel.dart';
import '../screens/tv/tv_streaming_screen.dart';
import '../services/api_service.dart';
import '../theme.dart';

class TvStreamingCard extends StatefulWidget {
  final List<TvChannel> initialChannels;

  const TvStreamingCard({
    super.key,
    this.initialChannels = const [],
  });

  @override
  State<TvStreamingCard> createState() => _TvStreamingCardState();
}

class _TvStreamingCardState extends State<TvStreamingCard> {
  List<TvChannel> _channels = [];
  TvChannel? _activeChannel;
  VideoPlayerController? _videoController;
  bool _isPlaying = false; // NO AUTOPLAY
  bool _isInitializing = false;
  bool _hasError = false;
  bool _isMuted = false;

  @override
  void initState() {
    super.initState();
    _channels = List.from(widget.initialChannels);
    if (_channels.isNotEmpty) {
      _activeChannel = _channels.first;
    } else {
      _fetchChannels();
    }
  }

  @override
  void didUpdateWidget(covariant TvStreamingCard oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.initialChannels.isNotEmpty && widget.initialChannels != oldWidget.initialChannels) {
      setState(() {
        _channels = List.from(widget.initialChannels);
        _activeChannel ??= _channels.first;
      });
    }
  }

  @override
  void dispose() {
    _videoController?.dispose();
    super.dispose();
  }

  Future<void> _fetchChannels() async {
    try {
      final res = await ApiService.instance.getTvChannels();
      final list = (res['channels'] as List<dynamic>? ?? [])
          .map((e) => TvChannel.fromJson(e as Map<String, dynamic>))
          .toList();
      if (!mounted) return;
      setState(() {
        _channels = list;
        if (_activeChannel == null && _channels.isNotEmpty) {
          _activeChannel = _channels.first;
        }
      });
    } catch (_) {}
  }

  Future<void> _startPlaying() async {
    if (_activeChannel == null) return;
    setState(() {
      _isPlaying = true;
      _isInitializing = true;
      _hasError = false;
    });

    await _initVideoPlayer(_activeChannel!.streamUrl);
  }

  Future<void> _initVideoPlayer(String url) async {
    _videoController?.dispose();
    _videoController = null;

    if (url.isEmpty) {
      if (mounted) {
        setState(() {
          _isInitializing = false;
          _hasError = true;
        });
      }
      return;
    }

    try {
      final controller = VideoPlayerController.networkUrl(
        Uri.parse(url),
        videoPlayerOptions: VideoPlayerOptions(mixWithOthers: true),
      );

      await controller.initialize();
      if (!mounted) {
        controller.dispose();
        return;
      }

      await controller.setVolume(_isMuted ? 0.0 : 1.0);
      await controller.play();

      setState(() {
        _videoController = controller;
        _isInitializing = false;
        _hasError = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isInitializing = false;
        _hasError = true;
      });
    }
  }

  void _switchChannel(TvChannel channel) {
    if (_activeChannel?.id == channel.id) return;
    setState(() {
      _activeChannel = channel;
    });

    if (_isPlaying) {
      setState(() {
        _isInitializing = true;
        _hasError = false;
      });
      _initVideoPlayer(channel.streamUrl);
    }
  }

  void _toggleMute() {
    if (_videoController == null) return;
    setState(() {
      _isMuted = !_isMuted;
    });
    _videoController?.setVolume(_isMuted ? 0.0 : 1.0);
  }

  void _openFullStreaming() {
    // Pause mini player when navigating to full screen
    _videoController?.pause();
    setState(() => _isPlaying = false);

    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const TvStreamingScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_channels.isEmpty) return const SizedBox.shrink();

    final activeCh = _activeChannel ?? _channels.first;

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.border, width: 1),
        boxShadow: const [
          BoxShadow(
            color: Color(0x06000000),
            blurRadius: 12,
            offset: Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Row
          Row(
            children: [
              Container(
                width: 34,
                height: 34,
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFBFDBFE)),
                ),
                child: const Center(
                  child: Icon(Icons.live_tv_rounded, color: Color(0xFF2563EB), size: 18),
                ),
              ),
              const SizedBox(width: 10),
              const Expanded(
                child: Text(
                  'TV & Live Streaming',
                  style: TextStyle(
                    fontSize: 14.5,
                    fontWeight: FontWeight.w800,
                    color: AppColors.textPrimary,
                    letterSpacing: -0.2,
                  ),
                ),
              ),
              // Live Pulse Badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFDC2626),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CircleAvatar(radius: 3, backgroundColor: Colors.white),
                    SizedBox(width: 4),
                    Text(
                      'LIVE',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w900,
                        color: Colors.white,
                        letterSpacing: 0.4,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // 16:9 Video Player Box (No Autoplay Initial State)
          ClipRRect(
            borderRadius: BorderRadius.circular(14),
            child: AspectRatio(
              aspectRatio: 16 / 9,
              child: Container(
                color: const Color(0xFF0B0F19),
                child: Stack(
                  children: [
                    // Video View if Playing
                    if (_isPlaying && _videoController != null && _videoController!.value.isInitialized)
                      Center(
                        child: AspectRatio(
                          aspectRatio: _videoController!.value.aspectRatio,
                          child: VideoPlayer(_videoController!),
                        ),
                      ),

                    // Initial Poster / Overlay when NOT playing
                    if (!_isPlaying)
                      InkWell(
                        onTap: _startPlaying,
                        child: Container(
                          width: double.infinity,
                          height: double.infinity,
                          decoration: const BoxDecoration(
                            gradient: RadialGradient(
                              center: Alignment.center,
                              radius: 0.9,
                              colors: [Color(0xFF1E293B), Color(0xFF0F172A)],
                            ),
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              // Play Button Circle
                              Container(
                                width: 54,
                                height: 54,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: AppColors.primary,
                                  boxShadow: [
                                    BoxShadow(
                                      color: AppColors.primary.withValues(alpha: 0.4),
                                      blurRadius: 16,
                                      offset: const Offset(0, 4),
                                    ),
                                  ],
                                ),
                                child: const Center(
                                  child: Icon(Icons.play_arrow_rounded, color: Colors.white, size: 34),
                                ),
                              ),
                              const SizedBox(height: 10),
                              Text(
                                activeCh.name,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 13.5,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                              const SizedBox(height: 2),
                              const Text(
                                'Ketuk untuk Memutar Siaran Langsung',
                                style: TextStyle(
                                  color: Color(0xFF94A3B8),
                                  fontSize: 11,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),

                    // Loading State
                    if (_isPlaying && _isInitializing)
                      Container(
                        color: Colors.black87,
                        child: const Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              SizedBox(
                                width: 26,
                                height: 26,
                                child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.2),
                              ),
                              SizedBox(height: 8),
                              Text(
                                'Menghubungkan siaran...',
                                style: TextStyle(color: Colors.white70, fontSize: 11),
                              ),
                            ],
                          ),
                        ),
                      ),

                    // Error State
                    if (_isPlaying && _hasError)
                      Container(
                        color: Colors.black87,
                        padding: const EdgeInsets.all(16),
                        child: Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.error_outline_rounded, color: Colors.orangeAccent, size: 28),
                              const SizedBox(height: 6),
                              const Text(
                                'Siaran tidak dapat dimuat',
                                style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700),
                              ),
                              const SizedBox(height: 6),
                              ElevatedButton(
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppColors.primary,
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                  minimumSize: Size.zero,
                                ),
                                onPressed: _startPlaying,
                                child: const Text('Coba Lagi', style: TextStyle(fontSize: 11)),
                              ),
                            ],
                          ),
                        ),
                      ),

                    // Overlay Controls when Playing
                    if (_isPlaying && _videoController != null && _videoController!.value.isInitialized)
                      Positioned(
                        top: 8,
                        right: 8,
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            // Mute toggle
                            GestureDetector(
                              onTap: _toggleMute,
                              child: Container(
                                padding: const EdgeInsets.all(6),
                                decoration: BoxDecoration(
                                  color: Colors.black.withValues(alpha: 0.6),
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(
                                  _isMuted ? Icons.volume_off_rounded : Icons.volume_up_rounded,
                                  color: Colors.white,
                                  size: 16,
                                ),
                              ),
                            ),
                            const SizedBox(width: 6),
                            // Open Fullscreen button
                            GestureDetector(
                              onTap: _openFullStreaming,
                              child: Container(
                                padding: const EdgeInsets.all(6),
                                decoration: BoxDecoration(
                                  color: Colors.black.withValues(alpha: 0.6),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(
                                  Icons.fullscreen_rounded,
                                  color: Colors.white,
                                  size: 16,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
              ),
            ),
          ),
          const SizedBox(height: 10),

          // Horizontal Channel Selector Chips
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: _channels.map((ch) {
                final isSelected = activeCh.id == ch.id;
                return Padding(
                  padding: const EdgeInsets.only(right: 6),
                  child: InkWell(
                    onTap: () => _switchChannel(ch),
                    borderRadius: BorderRadius.circular(10),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: isSelected ? const Color(0xFFEFF6FF) : AppColors.bg,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color: isSelected ? const Color(0xFF3B82F6) : AppColors.border,
                          width: isSelected ? 1.2 : 1,
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.tv_rounded,
                            size: 13,
                            color: isSelected ? const Color(0xFF2563EB) : AppColors.textMuted,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            ch.name,
                            style: TextStyle(
                              fontSize: 11.5,
                              fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                              color: isSelected ? const Color(0xFF1D4ED8) : AppColors.textSecondary,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
          const SizedBox(height: 12),

          // Button: Lihat Semua Live Streaming
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: _openFullStreaming,
              style: OutlinedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 10),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                side: const BorderSide(color: AppColors.border),
                backgroundColor: AppColors.bg,
              ),
              icon: const Text(
                'Lihat Semua Live Streaming',
                style: TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w800,
                  color: AppColors.textPrimary,
                ),
              ),
              label: const Icon(Icons.arrow_forward_rounded, size: 16, color: AppColors.textPrimary),
            ),
          ),
        ],
      ),
    );
  }
}
