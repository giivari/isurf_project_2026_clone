import 'package:flutter/material.dart';

void main() {
  runApp(const IsurfApp());
}

class IsurfApp extends StatelessWidget {
  const IsurfApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'iSURF Smart Farming',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.green),
        useMaterial3: true,
      ),
      home: const DashboardScreen(),
    );
  }
}

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  // Placeholder for future API integration
  // Future<void> fetchSensorData() async { ... }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Theme.of(context).colorScheme.inversePrimary,
        title: const Text('iSURF Dashboard'),
      ),
      body: const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            Icon(Icons.eco, size: 80, color: Colors.green),
            SizedBox(height: 20),
            Text(
              'Selamat Datang di iSURF',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 10),
            Text(
              'Aplikasi sedang dalam tahap pengembangan (API Integration Phase).',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey),
            ),
          ],
        ),
      ),
    );
  }
}
