class JellyfinMovie {
  final String id;
  final String title;
  final String year;
  final double? rating;
  final String overview;
  final List<String> genres;
  final String duration;
  final String poster;
  final String backdrop;
  final String streamUrl;
  final String container;

  JellyfinMovie({
    required this.id,
    required this.title,
    this.year = '',
    this.rating,
    this.overview = '',
    this.genres = const [],
    this.duration = '',
    required this.poster,
    required this.backdrop,
    required this.streamUrl,
    this.container = 'mp4',
  });

  factory JellyfinMovie.fromJson(Map<String, dynamic> json) {
    final rawGenres = json['genres'] as List<dynamic>? ?? [];
    return JellyfinMovie(
      id: json['id']?.toString() ?? '',
      title: json['title']?.toString() ?? json['name']?.toString() ?? 'Untitled Movie',
      year: json['year']?.toString() ?? json['production_year']?.toString() ?? '',
      rating: json['rating'] != null ? double.tryParse('${json['rating']}') : null,
      overview: json['overview']?.toString() ?? '',
      genres: rawGenres.map((e) => e.toString()).toList(),
      duration: json['duration']?.toString() ?? '',
      poster: json['poster']?.toString() ?? '',
      backdrop: json['backdrop']?.toString() ?? json['poster']?.toString() ?? '',
      streamUrl: json['stream_url']?.toString() ?? json['streamUrl']?.toString() ?? '',
      container: json['container']?.toString() ?? 'mp4',
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'title': title,
        'year': year,
        if (rating != null) 'rating': rating,
        'overview': overview,
        'genres': genres,
        'duration': duration,
        'poster': poster,
        'backdrop': backdrop,
        'stream_url': streamUrl,
        'container': container,
      };
}
