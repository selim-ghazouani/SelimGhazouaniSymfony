<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use App\Form\BookType;
use App\Form\MinMaxType;
use App\Form\SearchBookType;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\PseudoTypes\True_;
use Symfony\Component\Form\Extension\Core\Type\SearchType;

class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }

   


    #[Route('/addformbook', name: 'addformbook')]
    public function addformbook(ManagerRegistry $managerRegistry,Request $req): Response
    {
        $em=$managerRegistry->getManager();
        $book= new Book();
        $form=$this->createForm(BookType::class,$book);
        $form->handleRequest($req);
        if ($form->isSubmitted() and $form->isValid() ){
            $author = $book->getAuthor();
            if ($book->isPublished()) { 
                $author->setNbBooks($author->getNbBooks() + 1);
            } 
         $em->persist($author);
        $em->persist($book);
        $em->flush();
        return $this->redirectToRoute('showdbbook');
        }
        return $this->renderForm('book/addformbook.html.twig', [
            'f'=>$form
        ]);
    }






    #[Route('/editbook/{id}', name: 'editbook')]
    public function editbook($id,BookRepository $bookRepository,ManagerRegistry $managerRegistry,Request $req): Response
    {
        //var_dump($id).die();
        $em=$managerRegistry->getManager();
        $dataid=$bookRepository->find($id);
        //var_dump($dataid).die();
        $form=$this->createForm(BookType::class,$dataid);
        $form->handleRequest($req);
        if ($form->isSubmitted() and $form->isValid()){
            $author = $dataid->getAuthor();
            if (!$dataid->isPublished()) { 
                $author->setNbBooks($author->getNbBooks() - 1);
            } 
            $em->persist($dataid);
            $em->flush();
            return $this->redirectToRoute('showdbbook');

        }
        return $this->renderForm('book/editbook.html.twig', [
            'f' => $form
        ]);
    }





    #[Route('/deletebook/{id}', name: 'deletebook')]
    public function deletebook($id,BookRepository $authorRepository,ManagerRegistry $managerRegistry): Response
    {
        //var_dump($id).die();
        $em=$managerRegistry->getManager();
        $dataid=$authorRepository->find($id);
        $author = $dataid->getAuthor();

        // Decrement the author's nbBooks
        $author->setNbBooks($author->getNbBooks() - 1);
        //var_dump($dataid).die();
        $em->remove($dataid);
        $em->flush();
        return $this->redirectToRoute('showdbbook');
    }







    

    #[Route('/list35', name: 'list35')]
    public function listBooksPublishedBefore2023WithAuthorsMoreThan35Books(BookRepository $bookRepository): Response
    {
        $books = $bookRepository->findBooksPublishedBefore2023WithAuthorsMoreThan35Books();

        return $this->render('book/list35.html.twig', [
            'books' => $books,
        ]);
    }





    #[Route('/showbyidauthor/{ref}', name: 'showbyidauthor')]
    public function showidbyauthor($ref,BookRepository $BookRepository , ManagerRegistry $managerRegistry): Response
    {
        $em=$managerRegistry->getManager();
        $book = $BookRepository->find($ref);
        $em->persist($book);
        $em->flush();
        return $this->render('book/showbyidauthor.html.twig', [
            'book' => $book,
        ]);
    }





    #[Route('/book/show/{id}', name: 'show_book')]
    public function showBook($id, ManagerRegistry $managerRegistry): Response
    {
        // Récupérez le livre depuis la base de données en utilisant Doctrine
        $entityManager =$managerRegistry->getManager();
        $bookRepository = $entityManager->getRepository(Book::class);
        $book = $bookRepository->find($id);

        if ($book === null) {
            throw $this->createNotFoundException('Le livre n\'existe pas.');
        }

        // Utilisez la méthode render pour afficher un template avec les détails du livre
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/deleteAuthorWith0ZeroBooks', name: 'deleteAuthorWith0ZeroBooks')]
    public function deleteAuthorWith0ZeroBooks(ManagerRegistry $managerRegistry,BookRepository $bookRepository): Response
    {

        $entityManager =$managerRegistry->getManager();

        $authorstodelete = $bookRepository->DeleteAuthorWith0Books();
        $entityManager->flush();

        return $this->redirectToRoute('showdbbook');

    }





    #[Route('/deleteZeroBooks', name: 'deleteZeroBooks')]
    public function deleteZeroBooks(ManagerRegistry $managerRegistry): Response
    {
        $entityManager =$managerRegistry->getManager();
        $authorRepository = $entityManager->getRepository(Author::class);
        $bookRepository = $entityManager->getRepository(Book::class);

        // Récupérer la liste des auteurs avec nb_books égal à zéro
        $authorsToDelete = $authorRepository->findBy(['nb_books' => 0]);

        foreach ($authorsToDelete as $author) {
            // Retrieve the associated books
            $books = $bookRepository->findBy(['author' => $author]);

            foreach ($books as $book) {
                $entityManager->remove($book);
            }

            $entityManager->remove($author);
        }

        $entityManager->flush();

        return $this->redirectToRoute('showdbbook');
    }


 #[Route('/updateCategoryForWilliamShakespeareBooks', name: 'updateCategoryForWilliamShakespeareBooks')]
    public function updateCategoryForWilliamShakespeareBooks(BookRepository $bookRepository, ManagerRegistry $managerRegistry)
    {
        $entityManager =$managerRegistry->getManager();

        $williamShakespeareBooks = $bookRepository->updateCategoryForWilliamShakespeareBooks();

        foreach ($williamShakespeareBooks as $book) {
            $book->setCategory('Romance');
            $entityManager->persist($book);
        }

        $entityManager->flush();

        return $this->redirectToRoute('showdbbook'); // Redirect to the list of books or another appropriate route
    }
    

    #[Route('/listBooksPublishedBetweenDates', name: 'listBooksPublishedBetweenDates')]
    public function listBooksPublishedBetweenDates(BookRepository $bookRepository)
    {
        $books = $bookRepository->findBooksPublishedBetweenDates();

        return $this->render('book/listBooksPublishedBetweenDates.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/ShowBookOrderByAuthor', name: 'ShowBookOrderByAuthor')]
    public function ShowBookOrderByAuthor(BookRepository $bookRepository)
    {
        $books = $bookRepository->ShowBookOrderByAuthor();

        return $this->render('book/showdbbook.html.twig', [
            'books' => $books,
        ]);
    }

    



    #[Route('/showdbbook', name: 'showdbbook')]
    public function publishedBooks(BookRepository $bookRepository, Request $req): Response
    {
       $books = $bookRepository->ShowBookOrderByAuthor();
        // Récupérez la liste des livres publiés
        $book = $bookRepository->findBy(['published' => true]);
       
        $totalBooks = $bookRepository->countBooksInScienceFictionCategory();



        $form=$this->createForm(SearchBookType::class,);     
   $form->handleRequest($req);
   if ($form->isSubmitted()){
   $ref = $form->get('ref')->getData();
   
   $book = $bookRepository->findByReference($ref);
   } 
         if ($book === null) {
            throw $this->createNotFoundException('Aucun Livre.');
        }


        $publishedCount = $bookRepository->count(['published' => true]);
        $unpublishedCount = $bookRepository->count(['published' => false]);
        

        return $this->renderForm('book/showdbbook.html.twig', [
            'book' => $book,
            'books' =>$books,
            'f' =>$form,
            'totalBooks'=>$totalBooks,
            'publishedCount' => $publishedCount,
            'unpublishedCount' => $unpublishedCount,
        ]);
    }
}
