/// Типизированная модель data-payload'а push-уведомления.
///
/// Формат payload'а (со стороны Laravel):
/// ```json
/// {
///   "type": "news",          // или "order", "chat", "promo"
///   "entity_id": "123",
///   "screen": "/news/123",
///   "title": "...",
///   "body": "..."
/// }
/// ```
class NotificationPayload {
  final String? type;
  final String? entityId;
  final String? screen;
  final String? title;
  final String? body;
  final Map<String, dynamic> raw;

  const NotificationPayload({
    required this.type,
    required this.entityId,
    required this.screen,
    required this.title,
    required this.body,
    required this.raw,
  });

  factory NotificationPayload.fromData(Map<String, dynamic> data) {
    return NotificationPayload(
      type: data['type']?.toString(),
      entityId: data['entity_id']?.toString() ?? data['entityId']?.toString(),
      screen: data['screen']?.toString(),
      title: data['title']?.toString(),
      body: data['body']?.toString(),
      raw: data,
    );
  }
}
