class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final dynamic raw;

  const ApiException(this.message, {this.statusCode, this.raw});

  @override
  String toString() =>
      'ApiException(statusCode: $statusCode, message: $message)';
}

class NetworkException extends ApiException {
  const NetworkException(super.message, {super.raw}) : super(statusCode: null);
}

class UnauthorizedException extends ApiException {
  const UnauthorizedException([super.message = 'Unauthorized'])
      : super(statusCode: 401);
}
